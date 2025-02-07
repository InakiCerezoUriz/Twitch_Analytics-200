# **Twitch Analytics**
### Miembros:

    1. Cerezo Uriz, Iñaki
    2. Iribarren Ruiz, Beñat
    3. Merino Pinedo, Javier
    4. Nagore Irigoyen, Alejandro

### Pasos a seguir:

    1. Clonar respositorio de GitHub: https://github.com/InakiCerezoUriz/VyV-200
    2. Descargar servidor local (WAMPServer, XAMPP ...)
    3. Subir archivos a la carpeta /VAR/WWW/HTML del servidor local
    4. Abrir en un navegador localhost:PUERTO/Entrega01-TwitchAnalytics/analytics/...

### Opciones de respuesta Api
##### Caso de uso 1 - Consultar información de un streamer
  
http://localhost:PUERTO/Entrega01-TwitchAnalytics/analytics/user?id=TU_ID_DE_EJEMPLO

##### Caso de uso 2 - Consultar streams en vivo
  
http://localhost:PUERTO/Entrega01-TwitchAnalytics/analytics/streams

##### Caso de uso 3 - Consultar "Top streams enriquecidos"
  
http://localhost:PUERTO/Entrega01-TwitchAnalytics/analytics/streams/enriched?limit=TU_LIMIT_DE_EJEMPLO

#### Caso ded uso 4 - Consultar documentación del API

http://localhost:PUERTO/Entrega01-TwitchAnalytics/analytics/documentation
