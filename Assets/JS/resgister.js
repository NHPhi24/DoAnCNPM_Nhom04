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

// Kiểm tra độ mạnh mật khẩu
function checkPasswordStrength(password) {
    const strengthMeter = document.querySelector('.strength-meter');
    const strengthBars = document.querySelectorAll('.strength-bar');
    const strengthText = document.querySelector('.strength-text');

    // Reset
    strengthBars.forEach(bar => {
        bar.classList.remove('weak', 'medium', 'strong');
    });

    if (!password) {
        strengthText.textContent = '';
        return;
    }

    // Đánh giá độ mạnh
    let strength = 0;

    // Độ dài
    if (password.length >= 8) strength++;
    if (password.length >= 12) strength++;

    // Chứa chữ hoa
    if (/[A-Z]/.test(password)) strength++;

    // Chứa chữ thường
    if (/[a-z]/.test(password)) strength++;

    // Chứa số
    if (/[0-9]/.test(password)) strength++;

    // Chứa ký tự đặc biệt
    if (/[^A-Za-z0-9]/.test(password)) strength++;

    // Hiển thị kết quả
    if (strength <= 2) {
        strengthBars[0].classList.add('weak');
        strengthText.textContent = 'Mật khẩu yếu';
        strengthText.style.color = 'var(--error-color)';
    } else if (strength <= 4) {
        strengthBars[0].classList.add('weak');
        strengthBars[1].classList.add('medium');
        strengthText.textContent = 'Mật khẩu trung bình';
        strengthText.style.color = 'var(--warning-color)';
    } else {
        strengthBars[0].classList.add('weak');
        strengthBars[1].classList.add('medium');
        strengthBars[2].classList.add('strong');
        strengthText.textContent = 'Mật khẩu mạnh';
        strengthText.style.color = 'var(--success-color)';
    }
}

// Xử lý đăng ký
document.getElementById('registerForm').addEventListener('submit', function (e) {
    e.preventDefault();

    const firstName = document.getElementById('firstName').value;
    const lastName = document.getElementById('lastName').value;
    const email = document.getElementById('email').value;
    const username = document.getElementById('username').value;
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirmPassword').value;
    const termsChecked = document.querySelector('input[name="terms"]').checked;

    // Validate
    if (!firstName || !lastName || !email || !username || !password || !confirmPassword) {
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
    const userData = {
        firstName,
        lastName,
        email,
        username,
        password
    };

    console.log('Đăng ký với:', userData);

    // Hiển thị thông báo thành công
    alert('Đăng ký thành công! Tài khoản của bạn đã được tạo.');

    // Có thể redirect đến trang khác nếu cần
    // window.location.href = 'welcome.html';
});

// Theo dõi thay đổi mật khẩu để kiểm tra độ mạnh
document.getElementById('password').addEventListener('input', function (e) {
    checkPasswordStrength(e.target.value);
});

// Theo dõi thay đổi xác nhận mật khẩu
document.getElementById('confirmPassword').addEventListener('input', function (e) {
    const password = document.getElementById('password').value;
    const confirmPassword = e.target.value;

    if (password !== confirmPassword && confirmPassword.length > 0) {
        e.target.style.borderColor = 'var(--error-color)';
    } else {
        e.target.style.borderColor = 'var(--border-color)';
    }
});