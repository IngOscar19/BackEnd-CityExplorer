<h1 align="center">🌐 Laravel Backend API</h1>

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-10.x-red?logo=laravel&style=flat-square" />
  <img src="https://img.shields.io/badge/PHP-8.1-blue?logo=php&style=flat-square" />
  <img src="https://img.shields.io/badge/MySQL-Database-orange?logo=mysql&style=flat-square" />
  <img src="https://img.shields.io/badge/Sanctum-Auth-informational?style=flat-square" />
  <img src="https://img.shields.io/badge/Stripe-Integration-purple?logo=stripe&style=flat-square" />
</p>

<p align="center">
  <b>API RESTful desarrollada con Laravel para gestión de usuarios y lugares turísticos, con autenticación mediante Sanctum y pagos con Stripe.</b>
</p>

---

```bash
███████╗ █████╗ ███╗   ██╗ ██████╗ ███████╗██████╗ ██╗   ██╗
██╔════╝██╔══██╗████╗  ██║██╔═══██╗██╔════╝██╔══██╗╚██╗ ██╔╝
█████╗  ███████║██╔██╗ ██║██║   ██║█████╗  ██████╔╝ ╚████╔╝ 
██╔══╝  ██╔══██║██║╚██╗██║██║   ██║██╔══╝  ██╔═══╝   ╚██╔╝  
███████╗██║  ██║██║ ╚████║╚██████╔╝███████╗██║        ██║   
╚══════╝╚═╝  ╚═╝╚═╝  ╚═══╝ ╚═════╝ ╚══════╝╚═╝        ╚═╝   
```

---

# 🧾 Laravel Backend City Explorer - Tipos de usuarios como Anunciantes e Invitados, Lugares y Pagos con Stripe

Este proyecto es una API RESTful construida con **Laravel**, que permite la gestión de **usuarios** y **lugares turísticos**, integrando autenticación mediante **Sanctum** y procesamiento de pagos mediante **Stripe**.

---

## 🚀 Funcionalidades principales

- ✅ Registro y login de usuarios con autenticación **Sanctum**
- 🏝️ CRUD completo para **lugares turísticos**
- 👤 CRUD completo para **Anunciantes**
- 💳 Procesamiento de pagos usando **Stripe** (tarjeta de crédito/débito)
- 🔐 Acceso protegido mediante tokens
- 📄 Documentación y estructura organizada

---

## 📦 Requisitos

- PHP ^8.1
- Composer
- Docker
- MySQL
- Node.js y NPM (opcional si usas frontend)
- Stripe API Key

---

## 🛠 Instalación

1. Clona el repositorio:

```bash
git clone https://github.com/IngOscar19/BackEnd-CityExplorer.git
cd BackEnd-CityExplorer
```
2. Crear El contenedor para la Base de Datos en Docker:
    Guardar el archivo city.yml en una carpeta especifica
    Abrir Docker
   En Terminal (cmd)
```bash
cd Carpeta donde este el archivo city.yml
docker-compose -f City.yml up -d (Para comando basico)
Si se usa el archivo City.yml del proyecto clonado usar en la raiz del proyecto:
docker-compose -f City.yml build --no-cache


```  
3. Cargar el script de la base de datos:

Abrir el link del phpmyadmin
Ingresar con el usuario y contraseña que estan en el archivo city.yml
Copear las tablas y los inserts


4. Instala las dependencias:

```bash
composer require stripe/stripe-php

composer require laravel/sanctum

composer require intervention/image:^2.7
```

4. Copia el archivo `.env` y configura tus variables:

```bash
cp .env.example .env
```

5. Configura la conexión a la base de datos en tu `.env`

6. Ejecuta las migraciones :

```bash
php artisan migrate 
```

---

## 🔐 Autenticación con Sanctum

Este proyecto usa [Laravel Sanctum](https://laravel.com/docs/sanctum) para manejar la autenticación mediante tokens personales.  
Asegúrate de publicar el vendor y configurar correctamente el middleware.

---
## Comando para poder subir archivos desde el almacenamiento local
php artisan storage:link

## 💳 Pagos con Stripe

El proyecto está integrado con [Stripe](https://stripe.com) para pagos con tarjeta de crédito o débito.  
Asegúrate de configurar estas claves en tu `.env`:

```env
STRIPE_KEY=pk_test_XXXXXXXXXXXX
STRIPE_SECRET=sk_test_XXXXXXXXXXXX
```

Los pagos están relacionados con los lugares. Al registrar un lugar, se crea un registro de pago asociado.


## Integración de Pipedream

Para configurar la integración de Pipedream, puedes usar el siguiente endpoint:

[Pipedream Webhook](https://api.pipedream.com)

Esta se agrega en el archivo .env del proyecto

---

## 📂 Estructura del proyecto

```
app/
├── Http/
│   ├── Controllers/
|   |              ├── Controllers/
|   |                  ├── CategoriaLugarController.php
|   |                  ├── ComentarioController.php
|   |                  ├── FavoritosController.php
│   │                  ├── DireccionController.php
│   │                  ├── ListaController.php
|   |                  ├── ListaLugarController.php
│   │                  ├── LugarController.php
|   |                  ├── PagoController.php
|   |                  ├── RolController.php
│   │                  └── UsuarioController.php
│   └── Middleware/
├── Models/
│   ├── CategoriaLugar.php
│   ├── Comentario.php
│   ├── Favoritos.php
│   ├── Direccion.php
│   ├── Lista.php
│   ├── ListaLugar.php
│   ├── Lugar.php
│   ├── Pago.php
│   ├── Rol.php
│   └── Usuario.php
routes/
├── api.php
├── web.php
database/
├── migrations/
├── seeders/
```

---

## 📮 Rutas principales (API)

| Método | Ruta                          | Descripción                       |
|--------|------------------------------ |---------------------------------- |
| POST   | /api/user/login               | Iniciar sesión                    |
| POST   | /api/user/register            | Registrar usuario                 |
| GET    | /api/lugar                    | Obtener todos los lugares Activos |
| GET    | /api/lugar                    | Obtener todos los lugares Activos |
| POST   | /api/lugar/con-direccion      | Crear nuevo lugar con direccion   |
| PATCH  | /api/lugar/{id}               | Editar lugar                      |
| DELETE | /api/lugar/{id}               | Eliminar lugar                    |
| POST   | /api/pago/pagar               | Procesar pago con Stripe          |

---


---

## 👨‍💻 Autor

Equipo de City Explorer
Oscar Martin Espinosa Romero
Jose Manuel Garcia Morales
Miguel Angel Diaz Rivera

Proyecto académico de backend con Laravel + Sanctum + Stripe + PipeDreamm

---

## 📄 Licencia

Este proyecto está bajo la licencia MIT.

---

## Se prohíbe estrictamente cualquier copia, modificación no autorizada de la Aplicación o de sus marcas, intentos de extraer el código fuente, traducir o crear versiones derivadas. El contenido y las marcas se proporcionan "TAL CUAL" para su información y uso personal, no comercial.

---
