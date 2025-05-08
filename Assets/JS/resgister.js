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

    strengthBars.forEach(bar => {
        bar.classList.remove('weak', 'medium', 'strong');
    });

    if (!password) {
        strengthText.textContent = '';
        return;
    }

    let strength = 0;
    if (password.length >= 8) strength++;
    if (password.length >= 12) strength++;
    if (/[A-Z]/.test(password)) strength++;
    if (/[a-z]/.test(password)) strength++;
    if (/[0-9]/.test(password)) strength++;
    if (/[^A-Za-z0-9]/.test(password)) strength++;

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