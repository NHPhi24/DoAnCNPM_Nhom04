function togglePasswordVisibility() {
    const passwordInput = document.getElementById('password');
    const toggleIcon = document.querySelector('.toggle-password i');

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

document.getElementById('loginForm').addEventListener('submit', function (e) {
    e.preventDefault();

    const username = document.getElementById('username').value;
    const password = document.getElementById('password').value;

    // Xử lý đăng nhập ở đây
    console.log('Đăng nhập với:', { username, password });

    // Hiển thị thông báo đăng nhập thành công (tạm thời)
    alert('Đăng nhập thành công! (Đây chỉ là demo)');

    // Chuyển hướng sau khi đăng nhập (ví dụ)
    // window.location.href = 'dashboard.html';
});