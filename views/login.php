<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Tortas y Tortas</title>
    <link rel="stylesheet" href="../assets/css/login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<div class="container">
  <div class="screen">
    <div class="screen__content">
      <form class="login" id="loginForm">
        <?php if (isset($_SESSION['error_login'])): ?>
          <div class="error-message">
            <?php 
              echo $_SESSION['error_login'];
              unset($_SESSION['error_login']);
            ?>
          </div>
        <?php endif; ?>
        <div class="login__field">
          <i class="login__icon fas fa-user"></i>
          <input type="email" name="usuario" class="login__input" placeholder="Usuario / Email" required>
        </div>
        <div class="login__field">
          <i class="login__icon fas fa-lock"></i>
          <input type="password" name="password" class="login__input" placeholder="Contraseña" required>
        </div>
        <button type="submit" class="button login__submit">
          <span class="button__text">Iniciar Sesión</span>
          <i class="button__icon fas fa-chevron-right"></i>
        </button>
      </form>
    </div>
    <div class="screen__background">
      <span class="screen__background__shape screen__background__shape4"></span>
      <span class="screen__background__shape screen__background__shape3"></span>
      <span class="screen__background__shape screen__background__shape2"></span>
      <span class="screen__background__shape screen__background__shape1"></span>
    </div>
  </div>
</div>
<script>
document.getElementById('loginForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const data = {
        usuario: formData.get('usuario'),
        password: formData.get('password')
    };

    try {
        const response = await fetch('../controllers/loginController.php?action=login', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });

        const result = await response.json();
        
        if (result.success) {
            window.location.href = result.redirect;
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: result.error || 'Error al iniciar sesión',
                confirmButtonColor: '#5D54A4'
            });
        }
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Error de conexión',
            confirmButtonColor: '#5D54A4'
        });
    }
});
</script>
</body>
</html>