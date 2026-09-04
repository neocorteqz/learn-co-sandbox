#!/usr/bin/env python3
"""Small control daemon for explicitly mapped Minecraft systemd units."""

import json
import os
import subprocess
from http.server import BaseHTTPRequestHandler, HTTPServer

TOKEN = os.environ.get("DAEMON_TOKEN", "")
UNITS = json.loads(os.environ.get("SERVER_UNITS", "{}"))
ACTIONS = {"start", "stop", "restart"}


class Handler(BaseHTTPRequestHandler):
    def _respond(self, status, payload):
        body = json.dumps(payload).encode()
        self.send_response(status)
        self.send_header("Content-Type", "application/json")
        self.send_header("Content-Length", str(len(body)))
        self.end_headers()
        self.wfile.write(body)

    def do_GET(self):
        if self.path == "/health":
            self._respond(200, {"ok": True})
            return

        parts = self.path.strip("/").split("/")
        if len(parts) == 3 and parts[0] == "servers" and parts[2] == "status":
            if self.headers.get("Authorization") != f"Bearer {TOKEN}" or not TOKEN:
                self._respond(401, {"error": "unauthorized"})
                return

            server_id = parts[1]
            unit = UNITS.get(server_id)
            if not unit:
                self._respond(404, {"error": "invalid server"})
                return

            result = subprocess.run(
                ["systemctl", "is-active", unit],
                capture_output=True,
                text=True,
                timeout=10,
            )
            status = result.stdout.strip() or "unknown"
            self._respond(200, {
                "ok": result.returncode == 0,
                "server_id": server_id,
                "status": status,
            })
            return

        self._respond(404, {"error": "route not found"})

    def do_POST(self):
        if self.headers.get("Authorization") != f"Bearer {TOKEN}" or not TOKEN:
            self._respond(401, {"error": "unauthorized"})
            return

        parts = self.path.strip("/").split("/")
        if len(parts) != 3 or parts[0] != "servers" or parts[2] != "actions":
            self._respond(404, {"error": "route not found"})
            return

        server_id, action = parts[1], self.headers.get("X-Action", "")
        unit = UNITS.get(server_id)
        if not unit or action not in ACTIONS:
            self._respond(400, {"error": "invalid server or action"})
            return

        result = subprocess.run(
            ["systemctl", action, unit], capture_output=True, text=True, timeout=30
        )
        self._respond(result.returncode == 0 and 202 or 502, {
            "ok": result.returncode == 0,
            "server_id": server_id,
            "action": action,
        })

    def log_message(self, *_):
        return


if __name__ == "__main__":
    HTTPServer((os.environ.get("DAEMON_HOST", "127.0.0.1"), int(os.environ.get("DAEMON_PORT", "8765"))), Handler).serve_forever()
