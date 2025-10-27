// Dangky page JavaScript - Form validation and password toggle

// Toggle password visibility
function togglePassword() {
    const passwordInput = document.getElementById('txtPass');
    const toggleIcon = document.getElementById('toggleIcon');

    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.className = 'bi bi-eye-slash';
    } else {
        passwordInput.type = 'password';
        toggleIcon.className = 'bi bi-eye';
    }
}

// Form validation
document.addEventListener('DOMContentLoaded', function () {
    const signupForm = document.getElementById('signupForm');
    const confirmPassInput = document.getElementById('confirmPass');

    if (signupForm) {
        signupForm.addEventListener('submit', function (e) {
            const password = document.getElementById('txtPass').value;
            const confirmPassword = document.getElementById('confirmPass').value;
            const terms = document.getElementById('terms').checked;

            // Check terms
            if (!terms) {
                e.preventDefault();
                alert('Bạn phải đồng ý với điều khoản sử dụng!');
                return;
            }

            // Check password match
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Mật khẩu xác nhận không khớp!');
                return;
            }

            // Check password length
            if (password.length < 6) {
                e.preventDefault();
                alert('Mật khẩu phải có ít nhất 6 ký tự!');
                return;
            }
        });
    }

    // Real-time password matching
    if (confirmPassInput) {
        confirmPassInput.addEventListener('input', function () {
            const password = document.getElementById('txtPass').value;
            const confirmPassword = this.value;

            if (confirmPassword && password !== confirmPassword) {
                this.style.borderColor = '#e53e3e';
            } else {
                this.style.borderColor = '#e2e8f0';
            }
        });
    }
});

// Show terms modal (simple alert for now)
function showTerms() {
    alert('Điều khoản sử dụng:\n\n1. Sử dụng ứng dụng một cách văn minh\n2. Không spam hay quấy rối người khác\n3. Cung cấp thông tin chính xác\n4. Tuân thủ pháp luật Việt Nam');
}

function showPrivacy() {
    alert('Chính sách bảo mật:\n\n1. Thông tin cá nhân được mã hóa và bảo vệ\n2. Không chia sẻ dữ liệu với bên thứ ba\n3. Bạn có quyền xóa tài khoản bất kỳ lúc nào\n4. Tuân thủ GDPR và luật bảo vệ dữ liệu');
}