// Chuyển đổi giữa form đăng nhập và đăng ký
function switchAuthForm(formType) {
    const loginForm = document.getElementById('loginFormContainer');
    const registerForm = document.getElementById('registerFormContainer');

    if (formType === 'register') {
        loginForm.classList.remove('active');
        registerForm.classList.add('active');
    } else {
        registerForm.classList.remove('active');
        loginForm.classList.add('active');
    }
}

// Hiển thị/ẩn mật khẩu
function togglePasswordVisibility(inputId) {
    const passwordInput = document.getElementById(inputId);
    const toggleIcon = passwordInput.nextElementSibling.querySelector('i');

    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.classList.remove('fa-eye');
        toggleIcon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        toggleIcon.classList.remove('fa-eye-slash');
        toggleIcon.classList.add('fa-eye');
    }
}

// Xử lý đăng nhập
document.getElementById('loginForm').addEventListener('submit', function (e) {
    e.preventDefault();

    const username = document.getElementById('loginUsername').value;
    const password = document.getElementById('loginPassword').value;

    // Validate
    if (!username || !password) {
        alert('Vui lòng điền đầy đủ thông tin đăng nhập');
        return;
    }

    // Xử lý đăng nhập ở đây
    console.log('Đăng nhập với:', { username, password });
    alert('Đăng nhập thành công! (Đây chỉ là demo)');

    // Chuyển hướng sau khi đăng nhập (ví dụ)
    // window.location.href = 'dashboard.html';
});

// Xử lý đăng ký
document.getElementById('registerForm').addEventListener('submit', function (e) {
    e.preventDefault();

    const username = document.getElementById('registerUsername').value;
    const email = document.getElementById('registerEmail').value;
    const password = document.getElementById('registerPassword').value;
    const confirmPassword = document.getElementById('registerConfirmPassword').value;
    const termsChecked = document.querySelector('input[name="terms"]').checked;

    // Validate
    if (!username || !email || !password || !confirmPassword) {
        alert('Vui lòng điền đầy đủ thông tin đăng ký');
        return;
    }

    if (password !== confirmPassword) {
        alert('Mật khẩu xác nhận không khớp');
        return;
    }

    if (password.length < 8) {
        alert('Mật khẩu phải có ít nhất 8 ký tự');
        return;
    }

    if (!termsChecked) {
        alert('Vui lòng đồng ý với điều khoản dịch vụ');
        return;
    }

    // Xử lý đăng ký ở đây
    console.log('Đăng ký với:', { username, email, password });
    alert('Đăng ký thành công! (Đây chỉ là demo)');

    // Tự động chuyển sang form đăng nhập sau khi đăng ký
    switchAuthForm('login');
});

// Khởi tạo form đăng nhập là active khi trang tải
document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('loginFormContainer').classList.add('active');
});