# 💜 Twitch Analytics

> API para el análisis de streamers, streams y tendencias en Twitch.

---

## 👥 Miembros del equipo

- 🧑‍💻 **Iñaki Cerezo Uriz**
- 🧑‍💻 **Beñat Iribarren Ruiz**
- 🧑‍💻 **Javier Merino Pinedo**
- 🧑‍💻 **Alejandro Nagore Irigoyen**

---

## 🚀 Puesta en marcha

### 🔧 Desarrollo local

1. **Clonar el repositorio**
   ```bash
   git clone https://github.com/InakiCerezoUriz/VyV-200
   cd VyV-200
   ```

2. **Lanzar el servidor local**
   ```bash
   php -S localhost:8000
   ```

3. **Abrir en el navegador**
   ```
   http://localhost/VyV-200/...
   ```

---

### 🌐 Producción

Accede a la aplicación desplegada en Render:

🔗 [https://twitch-analytics-200.onrender.com](https://twitch-analytics-200.onrender.com)

---

## 📡 Endpoints de la API

### 🔍 1. Consultar información de un streamer

```http
GET /analytics/user?id=TU_ID_DE_EJEMPLO
```

📌 Ejemplo:
```
http://localhost/VyV-200/analytics/user?id=123456
```

---

### 📺 2. Consultar streams en vivo

```http
GET /analytics/streams
```

📌 Ejemplo:
```
http://localhost/VyV-200/analytics/streams
```

---

### 🌟 3. Top Streams Enriquecidos

```http
GET /analytics/streams/enriched?limit=TU_LIMIT_DE_EJEMPLO
```

📌 Ejemplo:
```
http://localhost/VyV-200/analytics/streams/enriched?limit=10
```

---

### 👤 4. Registro de usuarios

```http
POST /register
```

📌 Ejemplo:
```
http://localhost/register
```

---

### 🔐 5. Obtener Token de Sesión

```http
POST /token
```

📌 Ejemplo:
```
http://localhost/token
```

---

### 🏆 6. Top of the Tops

```http
GET /analytics/topsofthetops?since=TU_TIEMPO_DE_EJEMPLO
```

📌 Ejemplo:
```
http://localhost/VyV-200/analytics/topsofthetops?since=3600
```

---

### 📄 7. Documentación de la API

Accede a la documentación completa aquí:

```
http://13.60.56.25/documentation/index.html
```

---

## 🛠 Tecnologías utilizadas

- PHP
- Twitch API
- Render (Hosting)

---
