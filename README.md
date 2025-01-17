# API PFG-IES-Abdera-Julen_David_E

## Descripción
Esta es la documentación de la API del proyecto PFG-IES-Abdera desarrollado por Julen Agüero Fernandez y David Escutia de Haro. Esta API permite gestionar los recursos del proyecto.

## Endpoints

### Obtener todos los recursos
```
GET /api/recursos
```
Devuelve una lista de todos los recursos disponibles.

### Obtener un recurso por ID
```
GET /api/recursos/{id}
```
Devuelve los detalles de un recurso específico.

### Crear un nuevo recurso
```
POST /api/recursos
```
Crea un nuevo recurso. El cuerpo de la solicitud debe contener los detalles del recurso en formato JSON.

### Actualizar un recurso
```
PUT /api/recursos/{id}
```
Actualiza los detalles de un recurso existente. El cuerpo de la solicitud debe contener los nuevos detalles del recurso en formato JSON.

### Eliminar un recurso
```
DELETE /api/recursos/{id}
```
Elimina un recurso específico.

> [!WARNING]
> Algunos endpoints pueden requerir autenticación. Asegúrate de incluir un token válido en el encabezado de la solicitud.

## Ejemplos de uso
### Obtener todos los recursos
```bash
curl -X GET "http://localhost/api/recursos"
```

### Crear un nuevo recurso
```bash
curl -X POST "http://localhost/api/recursos" -H "Content-Type: application/json" -d '{"nombre": "Nuevo Recurso", "descripcion": "Descripción del recurso"}'
```

## Contribuidores
- Julen Agüero Fernandez
- David Escutia de Haro

## Licencia
Este proyecto está licenciado bajo la Licencia MIT. Consulta el archivo LICENSE para más detalles.
