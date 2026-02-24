<template>
  <footer class="pie-pagina-principal">
    <div class="contenido-footer">
      
      <div class="bloque redes">
        <h4>Síguenos</h4>
        <img src="../assets/iconos/instagram.png" alt="icono-instagram" class="icono"><a href="https://www.instagram.com">proxi_markt</a>
        <img src="../assets/iconos/facebook.png" alt="icono-facebook" class="icono"><a href="https://facebook.com">Proxi Markt</a>
        <img src="../assets/iconos/x.png" alt="icono-x" class="icono"><a href="https://x.com">proxi_markt</a>
        <img src="../assets/iconos/tiktok.png" alt="icono-tiktok" class="icono"><a href="https://tiktok.com">proxi_markt</a>
      </div>

      <div class="bloque central">
        <div class="marca">
          <img src="../assets/logos/logo_peq.png" alt="logo pequeño"> 
          <h4>ProxiMarkt</h4>
        </div>
        <p class="derechos">Frutas y Verduras Frescas de tu vecindario</p>
        <p class="derechos pequeno">© 2026 ProxiMarkt. Todos los derechos reservados.</p>
      </div>

      <div class="bloque contacto">
        <h4>Ayuda y Contacto</h4>
        <img src="../assets/iconos/manual.png" alt="icono-manual" class="icono"><a href="../../ProxiMarkt.pdf" download="Manual_Usuario_ProxiMarkt.pdf">Manual de usuario de uso</a>
        <img src="../assets/iconos/email.png" alt="icono-email" class="icono"><a href="mailto:mercadoproximidad@gmail.com">mercadoproximidad@gmail.com</a>
        <img src="../assets/iconos/whatsapp.png" alt="icono-whatsapp" class="icono"><a href="tel:+34900000000">+34 900 000 000</a>
      </div>

    </div>
  </footer>
</template>

<style scoped>
.pie-pagina-principal {
  background: #FFFFFF;
  padding: 40px 50px;
  box-shadow: 0px -4px 15px -5px #CFCFCF;
  border-top: 1px solid #EEEEEE;
}

.contenido-footer {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  max-width: 1200px;
  margin: 0 auto;
  gap: 20px;
}

.bloque {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.redes, .contacto {
  display: grid !important; 
  grid-template-columns: min-content auto; 
  align-items: center;
  gap: 10px;
}

.redes h4, .contacto h4 {
  grid-column: 1 / -1; 
  color: #4CA626;
  font-size: 16px;
  margin-bottom: 5px;
  font-weight: 700;
}

.contacto {
  justify-content: end;
  text-align: left;
}

.bloque a, .bloque span {
  font-size: 14px;
  color: #555555;
  text-decoration: none;
  transition: color 0.3s;
}

.bloque a:hover {
  color: #4CA626;
}

.central {
  text-align: center;
  align-items: center;
}

.marca {
  font-size: 22px;
  font-weight: bold;
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 10px;
  color: #4CA626;
}

.marca img {
  width: 35px;
  height: 35px;
  border-radius: 8px;
}

.icono {
    width: 24px;
    height: 24px;
    object-fit: contain;
}

.derechos {
  font-size: 13px;
  color: #666666;
}

.derechos.pequeno {
  font-size: 11px;
  color: #999999;
}

@media (max-width: 767px) {
    .form-card {
        width: 95%;
        max-width: 400px;
        margin: 20px auto;
        padding: 20px;
    }

    .login-contenedor {
        padding: 15px;
    }

    .pie-pagina-principal { 
        padding: 30px 10px; 
    }

    .contenido-footer {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 40px;
    }

    .central {
        order: -1;
    }

    .redes {
        display: grid !important;
        grid-template-columns: repeat(4, 1fr) !important;
        justify-items: center;
        width: 100%;
        gap: 5px 5px;
    }

    .redes h4 {
        grid-column: 1 / -1;
        text-align: center;
        margin-bottom: 8px;
    }

    .redes .icono {
        grid-row: 2;
        margin-bottom: 2px;
    }

    .redes a {
        grid-row: 3;
        font-size: 10px;
        text-align: center;
    }

    .contacto {
        display: grid !important;
        grid-template-columns: 1fr 1fr !important;
        width: 100%;
        gap: 12px 10px;
        justify-items: center;
    }

    .contacto h4 {
        grid-column: 1 / -1;
        text-align: center;
        margin-bottom: 8px;
    }

    .contacto img[alt="icono-email"] {
        grid-row: 2;
        grid-column: 1;
    }

    .contacto a[href*="mailto"] {
        grid-row: 3;
        grid-column: 1;
        font-size: 10px;
        margin-top: -8px;
    }

    .contacto img[alt="icono-whatsapp"] {
        grid-row: 2;
        grid-column: 2;
    }

    .contacto a[href*="tel"] {
        grid-row: 3;
        grid-column: 2;
        font-size: 10px;
        margin-top: -8px;
    }

    .contacto img[alt="icono-manual"] {
        grid-row: 4;
        grid-column: 1 / -1;
        margin-top: 10px;
    }

    .contacto a:not([href*="mailto"]):not([href*="tel"]) { 
        grid-row: 5; 
        grid-column: 1 / -1; 
        font-size: 10px;
        margin-top: -8px;
    }

    .contacto .icono {
        width: 24px !important;
        height: 24px !important;
    }

    .contacto a {
        text-align: center;
        text-decoration: none;
        color: #555;
    }
}

@media (min-width: 768px) and (max-width: 1023px) {
    .form-card {
        max-width: 450px;
        margin: 40px auto;
    }

    .contenido-footer {
        flex-wrap: wrap;
        justify-content: space-around;
        gap: 30px;
    }

    .bloque {
        flex: 1 1 45%;
    }

    .central {
        order: 3;
        flex: 1 1 100%;
        margin-top: 20px;
    }
}

@media (min-width: 1024px) {
    .form-card {
        max-width: 500px;
        margin: 60px auto;
    }

    .contenido-footer { 
        flex-direction: row; 
        justify-content: space-between; 
        max-width: 1200px; 
        margin: 0 auto;
    }
}
</style>