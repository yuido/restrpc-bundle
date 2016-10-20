# YuidoRestRpcBundle

Este bundle proporciona las siguientes herramientas para facilitar la creación
de APIs REST RPC:

- Un controlador genérico con las operaciones CRUD con el que se puede gestionar
  cualquier tabla de una base de datos.

- Un servicio 'rest_rpc' que ofrece varias herramientas útiles en la creación de
  API's: validación de la request a través de json-schema, conversión del JSON
  que viene en la request a un array de PHP para facilitar su manipulación y 
  funciones para generar fácilmente respuestas de error y éxito.

La estrategia para crear API's basadas en este bundle se explica con un ejemplo
en el tutorial que puedes encontrar en ``Resources\doc``.

## Instalación

Añadir al proyecto el bundle:

    composer.phar require yuido/restrpc-bundle "^1.*"

Registrar el bundle en ``app/AppKernel.php``:

    new Yuido\RestRpcBundle\YuidoRestRpcBundle(),

Añadir las rutas en ``app/config/routing.yml``

    rest_rpc:
        resource: "@YuidoRestRpcBundle/Controller/"
        type:     annotation
  

Crear un directorio donde colocar los *json-schemas* que definirán el formato que 
han de cumplir los JSON's que vienen en las request de cada operación. Podemos
crearlo donde queramos. Un buen sitio puede ser ``app/config/schemas``.

Añadir al archivo ``app\config\config.yml`` la ruta donde se colocarán los 
json-schemas.

    yuido_rest_rpc:
        debug: true  
        json_schemas_dir: %kernel.root_dir%/config/schemas

El parámetro ``debug`` sirve para que los mensajes de error devueltos en las
respuestas de error, lleven más información acerca del mismo.

## Documentación

La documentación se encuentra en ``Resources/doc``.