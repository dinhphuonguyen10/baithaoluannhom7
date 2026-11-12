<?php
$appBaseUrl = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
$appBaseUrl = preg_replace('~/client$~', '', $appBaseUrl);
$apiUrl     = htmlspecialchars($appBaseUrl . '/api/auth/reset_password.php', ENT_QUOTES, 'UTF-8');
$homeUrl    = htmlspecialchars($appBaseUrl . '/index.php', ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Quên mật khẩu - ÉCRIN JEWELRY</title>
    <link rel="stylesheet" href="<?php echo htmlspecialchars($appBaseUrl . '/assets/style.css', ENT_QUOTES, 'UTF-8'); ?>">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #0d1b2a 0%, #1b2d42 50%, #0d1b2a 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', system-ui, sans-serif;
            padding: 20px;
        }

        .card {
            width: min(440px, 100%);
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.12);
            backdrop-filter: blur(16px);
            border-radius: 20px;
            padding: 40px 36px;
            box-shadow: 0 24px 60px rgba(0,0,0,0.4);
            color: #fff;
        }

        /* Icon + tiêu đề */
        .icon-wrap {
            width: 64px; height: 64px;
            background: linear-gradient(135deg, #c9a96e, #e8c97a);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 20px;
            font-size: 28px;
        }
        h1 {
            text-align: center;
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 6px;
        }
        .subtitle {
            text-align: center;
            color: rgba(255,255,255,0.55);
            font-size: 0.9rem;
            line-height: 1.5;
            margin-bottom: 28px;
        }

        /* Các bước */
        .steps {
            display: flex;
            align-items: center;
            gap: 0;
            margin-bottom: 28px;
        }
        .step {
            flex: 1;
            text-align: center;
            font-size: 0.75rem;
            color: rgba(255,255,255,0.4);
            position: relative;
        }
        .step-num {
            width: 28px; height: 28px;
            border-radius: 50%;
            border: 2px solid rgba(255,255,255,0.2);
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 4px;
            font-weight: 700;
            font-size: 0.8rem;
            transition: all 0.3s;
        }
        .step.active .step-num  { border-color: #c9a96e; color: #c9a96e; }
        .step.done .step-num    { background: #c9a96e; border-color: #c9a96e; color: #fff; }
        .step.active            { color: #c9a96e; }
        .step-line {
            flex: 1;
            height: 2px;
            background: rgba(255,255,255,0.15);
            transition: background 0.4s;
        }
        .step-line.done { background: #c9a96e; }

        /* Form */
        label {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            color: rgba(255,255,255,0.75);
            margin-bottom: 6px;
        }
        input[type="text"],
        input[type="tel"],
        input[type="password"] {
            width: 100%;
            padding: 11px 14px;
            border: 1px solid rgba(255,255,255,0.18);
            border-radius: 10px;
            background: rgba(255,255,255,0.08);
            color: #fff;
            font: inherit;
            font-size: 0.95rem;
            outline: none;
            transition: border-color 0.2s, background 0.2s;
            margin-bottom: 16px;
        }
        input::placeholder { color: rgba(255,255,255,0.3); }
        input:focus {
            border-color: #c9a96e;
            background: rgba(201,169,110,0.1);
        }

        button[type="submit"] {
            width: 100%;
            padding: 13px;
            border: 0;
            border-radius: 10px;
            background: linear-gradient(135deg, #c9a96e, #e8c97a);
            color: #0d1b2a;
            font: inherit;
            font-weight: 700;
            font-size: 0.95rem;
            cursor: pointer;
            transition: opacity 0.2s, transform 0.1s;
            margin-top: 4px;
        }
        button[type="submit"]:hover   { opacity: 0.9; }
        button[type="submit"]:active  { transform: scale(0.98); }
        button[type="submit"]:disabled { opacity: 0.55; cursor: not-allowed; }

        /* Thông báo */
        .msg {
            min-height: 20px;
            font-size: 0.85rem;
            text-align: center;
            margin-top: 12px;
            border-radius: 8px;
            padding: 0;
            transition: all 0.2s;
        }
        .msg.error   { color: #ff8080; background: rgba(255,80,80,0.1); padding: 8px 12px; }
        .msg.success { color: #6ee86e; background: rgba(80,200,80,0.1); padding: 8px 12px; }

        /* Link quay lại */
        .back {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: rgba(255,255,255,0.4);
            font-size: 0.85rem;
            text-decoration: none;
            transition: color 0.2s;
        }
        .back:hover { color: #c9a96e; }

        /* Done screen */
        .done-screen { text-align: center; display: none; }
        .done-screen .check {
            font-size: 56px;
            margin-bottom: 12px;
            animation: pop 0.4s ease;
        }
        @keyframes pop {
            0%   { transform: scale(0); opacity: 0; }
            70%  { transform: scale(1.2); }
            100% { transform: scale(1); opacity: 1; }
        }
        .done-screen h2 { font-size: 1.3rem; margin-bottom: 8px; }
        .done-screen p  { color: rgba(255,255,255,0.55); font-size: 0.9rem; margin-bottom: 24px; }
        .done-screen a {
            display: inline-block;
            padding: 11px 28px;
            background: linear-gradient(135deg, #c9a96e, #e8c97a);
            color: #0d1b2a;
            border-radius: 10px;
            font-weight: 700;
            text-decoration: none;
            transition: opacity 0.2s;
        }
        .done-screen a:hover { opacity: 0.85; }
    </style>
</head>
<body>

<div class="card">

    <!-- Icon -->
    <div class="icon-wrap">🔑</div>
    <h1>Quên mật khẩu?</h1>
    <p class="subtitle" id="subtitle">Nhập tên đăng nhập và số điện thoại để xác minh tài khoản.</p>

    <!-- Thanh bước -->
    <div class="steps" id="steps">
        <div class="step active" id="step1-label">
            <div class="step-num">1</div>
            Xác minh
        </div>
        <div class="step-line" id="line1"></div>
        <div class="step" id="step2-label">
            <div class="step-num">2</div>
            Đặt mật khẩu
        </div>
    </div>

    <!-- FORM BƯỚC 1: Xác minh -->
    <form id="form-verify">
        <label for="username">Tên đăng nhập</label>
        <input id="username" type="text" name="username" placeholder="Nhập tên đăng nhập" autocomplete="username" required>

        <label for="phone">Số điện thoại đã đăng ký</label>
        <input id="phone" type="tel" name="phone" placeholder="Ví dụ: 0901234567 (10 số)" autocomplete="tel" required>

        <button type="submit" id="btn-verify">Tiếp theo →</button>
        <p class="msg" id="msg-verify" role="alert"></p>
    </form>

    <!-- FORM BƯỚC 2: Đặt mật khẩu mới (ẩn ban đầu) -->
    <form id="form-reset" hidden>
        <label for="new-password">Mật khẩu mới (đúng 8 ký tự: hoa, thường, số, ký tự đặc biệt)</label>
        <input id="new-password" type="password" name="password" placeholder="Ví dụ: Abc@1234 (đúng 8 ký tự)" autocomplete="new-password" minlength="8" maxlength="8" required>

        <label for="confirm-password">Nhập lại mật khẩu mới (8 ký tự)</label>
        <input id="confirm-password" type="password" name="confirm_password" placeholder="Nhập lại mật khẩu mới" autocomplete="new-password" minlength="8" maxlength="8" required>

        <button type="submit" id="btn-reset">Đặt lại mật khẩu</button>
        <p class="msg" id="msg-reset" role="alert"></p>
    </form>

    <!-- Màn hoàn thành -->
    <div class="done-screen" id="done-screen">
        <div class="check">✅</div>
        <h2>Thành công!</h2>
        <p>Mật khẩu của bạn đã được đặt lại.<br>Hãy đăng nhập bằng mật khẩu mới.</p>
        <a href="<?php echo $homeUrl; ?>">Về trang chủ</a>
    </div>

    <a class="back" href="<?php echo $homeUrl; ?>">← Quay lại cửa hàng</a>

</div>

<script>
const API = '<?php echo $apiUrl; ?>';
let verifiedUsername = '';
let verifiedPhone    = '';

/* ---- Bước 1: Xác minh tài khoản ---- */
document.getElementById('form-verify').addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = document.getElementById('btn-verify');
    const msg = document.getElementById('msg-verify');
    const username = document.getElementById('username').value.trim();
    const phone    = document.getElementById('phone').value.trim().replace(/[\s().-]/g, '');

    if (!username || !phone) {
        return setMsg(msg, 'Vui lòng nhập đầy đủ thông tin.', 'error');
    }

    if (!/^0[0-9]{9}$/.test(phone)) {
        return setMsg(msg, 'Số điện thoại không hợp lệ (phải đúng 10 số, ví dụ: 0901234567).', 'error');
    }

    btn.disabled = true;
    btn.textContent = 'Đang kiểm tra...';
    setMsg(msg, '', '');

    try {
        const res  = await fetch(API, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'verify', username, phone })
        });
        const data = await res.json();

        if (!res.ok || data.status !== 'success') {
            throw new Error(data.message || 'Không thể xác minh.');
        }

        // Lưu lại để dùng ở bước 2
        verifiedUsername = username;
        verifiedPhone    = phone;

        // Chuyển sang bước 2
        goToStep2();

    } catch (err) {
        setMsg(msg, err.message, 'error');
    } finally {
        btn.disabled = false;
        btn.textContent = 'Tiếp theo →';
    }
});

/* ---- Bước 2: Đặt lại mật khẩu ---- */
document.getElementById('form-reset').addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn      = document.getElementById('btn-reset');
    const msg      = document.getElementById('msg-reset');
    const password = document.getElementById('new-password').value;
    const confirm  = document.getElementById('confirm-password').value;

    const isValidPassword = (pwd) => {
        return pwd.length === 8 &&
            /[a-z]/.test(pwd) &&
            /[A-Z]/.test(pwd) &&
            /[0-9]/.test(pwd) &&
            /[^a-zA-Z0-9]/.test(pwd);
    };

    if (!isValidPassword(password)) {
        return setMsg(msg, 'Mật khẩu phải đúng 8 ký tự, gồm chữ thường, chữ in hoa, số và ký tự đặc biệt (ví dụ: Abc@1234).', 'error');
    }
    if (password !== confirm) {
        return setMsg(msg, 'Mật khẩu xác nhận không khớp.', 'error');
    }

    btn.disabled = true;
    btn.textContent = 'Đang lưu...';
    setMsg(msg, '', '');

    try {
        const res  = await fetch(API, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'reset',
                username: verifiedUsername,
                phone:    verifiedPhone,
                password,
                confirm_password: confirm
            })
        });
        const data = await res.json();

        if (!res.ok || data.status !== 'success') {
            throw new Error(data.message || 'Không thể đặt lại mật khẩu.');
        }

        // Hiện màn hoàn thành
        showDone();

    } catch (err) {
        setMsg(msg, err.message, 'error');
    } finally {
        btn.disabled = false;
        btn.textContent = 'Đặt lại mật khẩu';
    }
});

/* ---- Helpers ---- */
function goToStep2() {
    document.getElementById('form-verify').hidden = true;
    document.getElementById('form-reset').hidden  = false;
    document.getElementById('subtitle').textContent = 'Nhập mật khẩu mới cho tài khoản của bạn.';

    // Cập nhật thanh bước
    document.getElementById('step1-label').className = 'step done';
    document.getElementById('step1-label').querySelector('.step-num').textContent = '✓';
    document.getElementById('line1').className = 'step-line done';
    document.getElementById('step2-label').className = 'step active';
}

function showDone() {
    document.getElementById('form-reset').hidden = true;
    document.getElementById('steps').hidden       = true;
    document.getElementById('subtitle').hidden    = true;
    const iconWrap = document.querySelector('.icon-wrap');
    if (iconWrap) iconWrap.style.display = 'none';
    const h1 = document.querySelector('h1');
    if (h1) h1.style.display = 'none';
    document.getElementById('done-screen').style.display = 'block';

    // Cập nhật bước 2 xong
    document.getElementById('step2-label').className = 'step done';
    document.getElementById('step2-label').querySelector('.step-num').textContent = '✓';
}

function setMsg(el, text, type) {
    el.textContent = text;
    el.className   = 'msg' + (type ? ' ' + type : '');
}
</script>

</body>
</html>
