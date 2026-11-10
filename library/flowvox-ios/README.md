# FlowvoxNative (iOS)

Hotwire Native shell for [Flowvox](../flowvox).

## 1. Start the Symfony backend

From the Flowvox project:

```bash
cd /Users/math/Sites/darkwood/flowvox
symfony server:start --no-tls --allow-all-ip
# → http://127.0.0.1:8000 (simulator)
# → http://<your-mac-ip>:8000 (physical iPhone)
```

Verify in a browser: `http://127.0.0.1:8000/`

> **Important:** Use **HTTP**, not HTTPS, unless you started Symfony with TLS and trust the certificate on the device.

## 2. Configure the app URL

Edit `FlowvoxNative/Info.plist` → `FLOWVOX_BASE_URL`:

| Target | Value |
|--------|--------|
| **Simulator** | `http://127.0.0.1:8000` |
| **Physical iPhone** | `http://192.168.x.x:8000` (your Mac's Wi‑Fi IP — **not** `127.0.0.1`) |

Find your Mac IP: System Settings → Network, or:

```bash
ipconfig getifaddr en1   # Wi‑Fi on most Macs (not en0)
```

**Dashboard vide mais worker actif ?** Le dashboard n’affiche que les sessions avec heartbeat &lt; 30 s. Ouvre directement la session : `http://192.168.1.11:8000/sessions/demo` (remplace l’IP et l’id de session).

## 3. Run in Xcode

Open `FlowvoxNative.xcodeproj`, build & run.

## Troubleshooting

| Error | Cause | Fix |
|-------|--------|-----|
| `Connection refused` (61) | Backend not running | `symfony server:start` |
| `Could not connect` to `https://127.0.0.1:8000` | Wrong scheme | Use `http://` in `FLOWVOX_BASE_URL` |
| Works in Simulator, fails on device | `127.0.0.1` = the phone | Set Mac LAN IP in Info.plist + `--allow-all-ip` |
| Path config warning | Remote JSON optional | Bundled `path-configuration.json` is enough in dev |

See also [flowvox/ios/README.md](../flowvox/ios/README.md).
