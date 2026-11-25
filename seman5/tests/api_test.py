"""Public HTTP contract tests. Run against a disposable Compose project only."""
import http.cookiejar
import json
import os
import time
import unittest
import urllib.error
import urllib.request

BASE = os.environ.get("API_URL", "http://127.0.0.1:18085")
EMAIL = "integration@example.test"
PASSWORD = "Public-Test-Fixture-2026!"


class ApiTest(unittest.TestCase):
    def setUp(self):
        self.jar = http.cookiejar.CookieJar()
        self.http = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(self.jar))

    def request(self, method, path, data=None, xsrf=True):
        headers = {"Content-Type": "application/json"}
        if xsrf:
            for cookie in self.jar:
                if cookie.name == "XSRF-TOKEN":
                    headers["X-XSRF-TOKEN"] = cookie.value
        req = urllib.request.Request(BASE + path, method=method, headers=headers,
                                     data=json.dumps(data).encode() if data is not None else None)
        try:
            response = self.http.open(req)
        except urllib.error.HTTPError as error:
            response = error
        raw = response.read().decode()
        try:
            body = json.loads(raw)
        except json.JSONDecodeError:
            body = raw
        return response.code, body, response.headers

    def login(self):
        self.assertEqual(self.request("GET", "/auth.php")[0], 200)
        before = next(c.value for c in self.jar if c.name == "UNIANDES_SESSION")
        code, body, headers = self.request("POST", "/auth.php",
                                         {"correo": EMAIL, "password": PASSWORD})
        self.assertEqual(code, 200, body)
        after = next(c.value for c in self.jar if c.name == "UNIANDES_SESSION")
        self.assertNotEqual(before, after)
        self.assertEqual(body["usuario"]["correo"], EMAIL)
        return headers

    def test_authentication_cookies_logout_and_retry(self):
        code, body, headers = self.request("GET", "/auth.php")
        self.assertEqual((code, body), (200, {"usuario": None}))
        cookies = headers.get_all("Set-Cookie")
        session = next(c for c in cookies if c.startswith("UNIANDES_SESSION="))
        self.assertIn("HttpOnly", session)
        self.assertIn("SameSite=Lax", session)
        self.assertEqual(self.request("GET", "/api.php")[0], 401)
        self.assertEqual(self.request("POST", "/auth.php",
                         {"correo": EMAIL, "password": "wrong"})[0], 401)
        self.login()
        self.assertEqual(self.request("GET", "/auth.php")[1]["usuario"]["correo"], EMAIL)
        self.assertEqual(self.request("DELETE", "/auth.php", xsrf=False)[0], 403)
        self.assertEqual(self.request("GET", "/api.php")[0], 200)
        self.assertEqual(self.request("DELETE", "/auth.php")[0], 200)
        self.assertFalse(any(c.name in ("UNIANDES_SESSION", "XSRF-TOKEN") for c in self.jar))
        self.assertEqual(self.request("GET", "/api.php")[0], 401)

    def test_csrf_required_for_login(self):
        self.request("GET", "/auth.php")
        self.assertEqual(self.request("POST", "/auth.php",
                         {"correo": EMAIL, "password": PASSWORD}, xsrf=False)[0], 403)
        self.assertEqual(self.request("GET", "/auth.php")[1], {"usuario": None})

    def test_crud_validation_and_rejected_writes(self):
        self.login()
        original = self.request("GET", "/api.php")[1]
        record = {"nombre": "Ana", "apellido": "Pérez", "correo": "ana@example.test",
                  "telefono": "0991234567"}
        self.assertEqual(self.request("POST", "/api.php", record, xsrf=False)[0], 403)
        bad = dict(record, correo="invalid")
        self.assertEqual(self.request("POST", "/api.php", bad)[0], 400)
        self.assertEqual(self.request("GET", "/api.php")[1], original)
        code, body, _ = self.request("POST", "/api.php", record)
        self.assertEqual(code, 201, body)
        identifier = body["id"]
        self.assertEqual(self.request("GET", f"/api.php?id={identifier}")[1],
                         dict(record, id=identifier))
        self.assertEqual(self.request("POST", "/api.php", record)[0], 409)
        record["nombre"] = "Ana María"
        self.assertEqual(self.request("PUT", f"/api.php?id={identifier}", record)[0], 200)
        self.assertEqual(self.request("GET", f"/api.php?id={identifier}")[1]["nombre"], "Ana María")
        self.assertEqual(self.request("GET", "/api.php?id=1oops")[0], 400)
        self.assertEqual(self.request("DELETE", "/api.php?id=1oops")[0], 400)
        self.assertEqual(self.request("GET", f"/api.php?id={identifier}")[1]["nombre"], "Ana María")
        self.assertEqual(self.request("DELETE", f"/api.php?id={identifier}")[0], 200)
        self.assertEqual(self.request("GET", f"/api.php?id={identifier}")[0], 404)
        self.assertEqual(self.request("GET", "/api.php")[1], original)
        # Rejected inserts and duplicate input must not consume an identity.
        code, body, _ = self.request("POST", "/api.php", record)
        self.assertEqual(code, 201)
        self.assertEqual(body["id"], identifier + 1)
        self.request("DELETE", f"/api.php?id={body['id']}")

    def test_session_expiration(self):
        self.login()
        time.sleep(int(os.environ.get("TEST_TTL", "3")) + 1)
        self.assertEqual(self.request("GET", "/api.php")[0], 401)
        self.assertEqual(self.request("GET", "/auth.php")[1], {"usuario": None})


if __name__ == "__main__":
    unittest.main(verbosity=2)
