# Base de Dades: ProxiMarkt 🛒🍅

## 📖 Descripció general
La base de dades de ProxiMarkt servix per a guardar i organitzar tota la informació que necessita la plataforma per a funcionar bé. El seu objectiu és fer possible la comunicació entre compradors i venedors i gestionar coses com els productes, les reserves o els punts de lliurament.

- Motor: Mysql
- Versió: 8

## 📊 Diagrama Entitat - Relació amb atributs
![Diagrama Entitat Relació amb atributs](/img/diagrama.jpg)

### 🔗 Relacions
    Ejemplo para saber como documentar una relación:
    Productos <---> Usuarios (1:N)

    Un Usuario puede realizar uno o varios Pedidos, y cada Pedido pertenece a un solo Usuario.

### 🏷️ Atributs