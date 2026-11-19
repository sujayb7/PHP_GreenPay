# GreenPay: intentional bugs (presenter notes)

GreenPay is a **training app**. It contains deliberate, well-known security bugs so ResolveAI can investigate them from the code and the logs. Do not copy this code into a real app, and never deploy GreenPay.

Each bug below lists where it is, the commit that introduced it, the log evidence it produces, and the fix ResolveAI should recommend.

## 1. SQL injection + weak hashing in login (critical)

- **Where:** `src/Auth.php::attempt()` — commit `PAY-318 hotfix`.
- **What:** the username and an `md5()` of the password are concatenated straight into a SQL string. An input like `admin' --` signs in as admin with no password. `md5` password hashes are also trivially crackable and collide.
- **Evidence (`logs/incidents/login-bypass.log`):** a sign-in for username `admin'...` from one IP, immediately followed by large transfers out of `ACC-1001`.
- **Fix:** use a prepared statement with a bound `:username`, and `password_hash()` / `password_verify()` with bcrypt. Revert the "single query" change.

## 2. Session fixation (high)

- **Where:** `src/Auth.php::login()` — the same commit removed `session_regenerate_id(true)`.
- **What:** the session id is kept across the privilege change at login, so an attacker who fixes a victim's session id can ride the authenticated session.
- **Fix:** regenerate the session id on every login.

## 3. PHP type-juggling auth bypass in the balance API (high)

- **Where:** `public/api/balance.php` — commit `Add balance API`. The API key is stored as `md5('240610708')`, a "magic hash" that begins `0e…`, and the check uses `!=` (loose comparison).
- **What:** PHP's `==`/`!=` treats two `0e…` strings as numbers, so `key=0` (or any `0e…` value) equals the stored key. `GET /api/balance.php?account=ACC-9000&key=0` returns the treasury balance.
- **Evidence (`logs/incidents/treasury-api.log`):** repeated `balance api ok` for `ACC-9000` from an unexpected IP with no matching `login ok`.
- **Fix:** compare with `hash_equals()` (strict), and store real random keys, not an md5 magic hash.

## 4. Reflected XSS + open redirect on the login page (medium)

- **Where:** `public/login.php` prints `$_GET['msg']` unescaped and redirects to an unchecked `$_GET['next']`; `src/Auth.php::requireUser()` builds those links.
- **What:** `login.php?msg=<script>…</script>` runs attacker script; `next=https://evil.example` sends the user off-site after login.
- **Fix:** escape `msg` with `e()`, and only allow `next` values that start with `/` (a local path).

## 5. Debug mode on in production (medium)

- **Where:** `config/config.php` — commit `PAY-311` set `'debug' => true`.
- **What:** stack traces and SQL text are shown to users, leaking schema and file paths. It also turns bug #1's SQL errors into a full disclosure.
- **Fix:** set `'debug' => false` (or drive it from an environment variable that is off in production).

## How a presenter stages an incident

These are read-only, local-only steps against your own `http://127.0.0.1:8080`:

- **Login probe:** sign in with username `admin'--` and any password, and note you reach the dashboard. (Shows bug #1.)
- **Magic hash:** open `http://127.0.0.1:8080/api/balance.php?account=ACC-9000&key=0` and note it returns the balance. (Shows bug #3.)

Then point ResolveAI's GitHub source at this repo and investigate the matching incident. The pre-written logs in `logs/incidents/` can be copied into the log folder ResolveAI reads, so the evidence is present without running anything.
