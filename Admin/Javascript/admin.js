// 1. UTILITY FUNCTIONS

function previewImage(fileName, imgId) {
    const img = document.getElementById(imgId);
    const imageBase = '../images/';
    
    if (fileName && fileName.trim()) {
        img.src = imageBase + fileName.trim();
        img.classList.remove('hide');
        img.onerror = () => {
            img.classList.add('hide');
            console.log('Could not load image:', fileName);
        };
    } else {
        img.classList.add('hide');
    }
}

  // Close category edit modal
 
function closeCatEdit() {
    const modal = document.getElementById('catEditModal');
    if (modal) {
        modal.style.display = 'none';
    }
    window.location = 'category.php';
}

   // Close edit modal for menu items
 
function closeEdit() {
    const modal = document.querySelector('.modal-overlay');
    if (modal) {
        modal.style.display = 'none';
    }
    window.location = window.location.pathname;
}

  //Confirm action with browser's native confirm

function confirmAction(message) {
    return confirm(message);
}

 //Check password strength (simple validation)

function checkStrength(input) {
    const pass = input.value;
    if (pass.length < 6) {
        input.style.borderColor = '#dc3545';
    } else if (pass.length < 8) {
        input.style.borderColor = '#ffc107';
    } else {
        input.style.borderColor = '#28a745';
    }
}

  // 2. LOGIN MODAL FUNCTIONALITY

   //Initialize login modal functionality
 
function initLoginModal() {
    const loginModal = document.getElementById('loginModal');
    const forgotModal = document.getElementById('forgotModal');
    const openLoginBtn = document.getElementById('openLoginBtn');
    const closeLoginBtn = document.getElementById('closeLoginBtn');
    const openForgotBtn = document.getElementById('openForgotBtn');
    const closeForgotBtn = document.getElementById('closeForgotBtn');
    
    if (!loginModal || !openLoginBtn) return;
    
    // Open login modal
    openLoginBtn.onclick = () => {
        loginModal.classList.add('active');
        document.body.style.overflow = 'hidden';
    };
    
    // Close login modal
    closeLoginBtn.onclick = () => {
        loginModal.classList.remove('active');
        document.body.style.overflow = 'auto';
    };
    
    // Close modal when clicking outside
    loginModal.onclick = (e) => {
        if (e.target === loginModal) {
            loginModal.classList.remove('active');
            document.body.style.overflow = 'auto';
        }
    };
    
    // Forgot password modal
    if (openForgotBtn && forgotModal) {
        openForgotBtn.onclick = (e) => {
            e.preventDefault();
            loginModal.classList.remove('active');
            forgotModal.classList.add('active');
            document.body.style.overflow = 'hidden';
        };
        
        closeForgotBtn.onclick = () => {
            forgotModal.classList.remove('active');
            document.body.style.overflow = 'auto';
        };
        
        forgotModal.onclick = (e) => {
            if (e.target === forgotModal) {
                forgotModal.classList.remove('active');
                document.body.style.overflow = 'auto';
            }
        };
    }
    
    // Handle login form submission
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            const btn = this.querySelector('button[type="submit"]');
            if (btn) {
                btn.disabled = true;
                const originalText = btn.innerHTML;
                btn.innerHTML = '🔄 Authenticating...';
                
                // Reset button after 2 seconds (in case of error)
                setTimeout(() => {
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                }, 2000);
            }
        });
    }
}

      // 3. INITIALIZATION

   //Main initialization function

document.addEventListener('DOMContentLoaded', function() {
    console.log('Admin panel initialized');
    
    // Auto-hide alerts after 5 seconds
    setTimeout(() => {
        document.querySelectorAll('.alert').forEach(alert => {
            alert.style.opacity = '0';
            alert.style.transition = 'opacity 0.5s ease';
            setTimeout(() => {
                if (alert.parentNode) {
                    alert.parentNode.removeChild(alert);
                }
            }, 500);
        });
    }, 5000);
    
    // Password confirmation validation
    document.querySelectorAll('form').forEach(form => {
        const password = form.querySelector('input[name="password"]');
        const confirmPassword = form.querySelector('input[name="confirm_password"]');
        
        if (password && confirmPassword) {
            form.addEventListener('submit', function(e) {
                if (password.value !== confirmPassword.value) {
                    e.preventDefault();
                    alert('Passwords do not match!');
                    password.focus();
                }
            });
        }
    });
    
    // Initialize login modal if on login page
    if (document.getElementById('loginModal')) {
        initLoginModal();
    }
    
    // Auto-show edit modal if URL has edit parameter
    if (window.location.hash.includes('editModal') || window.location.hash.includes('catEditModal')) {
        const modalId = window.location.hash.substring(1);
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove('hide');
        }
    }
});