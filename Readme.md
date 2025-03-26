# **Twitch Analytics**
### Miembros:

    1. Cerezo Uriz, Iñaki
    2. Iribarren Ruiz, Beñat
    3. Merino Pinedo, Javier
    4. Nagore Irigoyen, Alejandro

### Pasos a seguir:

    1. Clonar respositorio de GitHub: https://github.com/InakiCerezoUriz/VyV-200
    2. Descargar servidor local (WAMPServer, XAMPP, php ...)
    3. Subir archivos a la carpeta /VAR/WWW/HTML del servidor local
    4. Abrir en un navegador localhost/VyV-200/...

### Ejecutar en producción
    
    Para poder ejecutar ejecutar las llamadas en producción hay que utilizar el link:
    http://13.60.56.25/...

### Opciones de respuesta Api
##### Caso de uso 1 - Consultar información de un streamer
  
http://localhost/VyV-200/analytics/user?id=TU_ID_DE_EJEMPLO

##### Caso de uso 2 - Consultar streams en vivo
  
http://localhost/VyV-200/analytics/streams

##### Caso de uso 3 - Consultar "Top streams enriquecidos"
  
http://localhost/VyV-200/analytics/streams/enriched?limit=TU_LIMIT_DE_EJEMPLO

##### Caso de uso 4 - Registro de usuarios

http://localhost/register

##### Caso de uso 5 - Obtención de Token de Sesión

http://localhost/token

##### Caso de uso 6 - Top of the Tops

http://localhost/VyV-200/analytics/topsofthetops?since=TU_TIEMPO_DE_EJEMPLO

#### Caso ded uso 7 - Consultar documentación del API

http://localhost/VyV-200/analytics/documentation/index.html
