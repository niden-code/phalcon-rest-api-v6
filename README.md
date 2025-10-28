# REST API with Phalcon v6

A REST API developed with Phalcon v6

The directory structure for this projects follows the recommendations of [pds/skeleton][pds_skeleton]

# Folders
The folders contain:

- `bin`: empty for now, we might use it later on
- `config`: .env configuration files for CI and example
- `docs`: documentation (TODO)
- `public`: entry point of the application where `index.php` lives
- `resources`: stores database migrations and docker files for local development
- `src`: source code of the project
- `storage`: various storage data such as application logs
- `tests`: tests

# Code organization
The application follows the [ADR pattern][adr_pattern] where the application is split into an `Action` layer, the `Domain` layer and a `Responder` layer.

## Action
The folder (under `src/`) contains the handler that is responsible for receiving data from the Domain and injecting it to the Responder so that the response can be generated. It also collects all the Input supplied by the user and injects it into the Domain for further processing.

## Domain

The Domain is organized in folders based on their use. The `Components` folder contains components that are essential to the operation of the application, while the `Services` folder contains classes that map to endpoints of the application

### Container

The application uses the `Phalcon\Di\Di` container with minimal components lazy loaded. Each non "core" component is also registered there (i.e. domain services, responder etc.) and all necessary dependencies are injected based on the service definitions.

Additionally there are two `Providers` that are also registered in the DI container for further functionality. The `ErrorHandlerProvider` which caters for the starting up/shut down of the application and error logging, and the very important `RoutesProvider` which handles registering all the routes that the application serves.

### Enums

There are several enumerations present in the application. Those help with common values for tasks. For example the `FlagsEnum` holds the values for the `co_users.usr_status_flag` field. We could certainly introduce a lookup table in the database for "status" and hold the values there, joining it to the `co_users` table with a lookup table. However, this will introduce an extra join in our query which will inevitable reduce performance. Since the `FlagsEnum` can keep the various statuses, we keep everything in code instead of the database. Thorough tests for enumerations ensure that if a change is made in the future, tests will fail, so that database integrity can be kept.

The `RoutesEnum` holds the various routes of the application. Every route is represented by a specific element in the enumeration and the relevant prefix/suffix are calculated for each endpoint. Also, each endpoint is mapped to a particular service, registered in the DI container, so that the action handler can invoke it when the route is matched.

Finally, the `RoutesEnum` also holds the middleware array, which defines their execution and the "hook" they will execute in (before/after).

### Middleware

There are several middleware registered for this application and they are being executed one after another (order matters) before the action is executed. As a result, the application will stop executing if an error occurs, or if certain validations fail. 

The middleware execution order is defined in the `RoutesEnum`. The available middleware is:

- [NotFoundMiddleware.php](src/Domain/Components/Middleware/NotFoundMiddleware.php)
- [HealthMiddleware.php](src/Domain/Components/Middleware/HealthMiddleware.php)
- [ValidateTokenClaimsMiddleware.php](src/Domain/Components/Middleware/ValidateTokenClaimsMiddleware.php)
- [ValidateTokenPresenceMiddleware.php](src/Domain/Components/Middleware/ValidateTokenPresenceMiddleware.php)
- [ValidateTokenRevokedMiddleware.php](src/Domain/Components/Middleware/ValidateTokenRevokedMiddleware.php)
- [ValidateTokenStructureMiddleware.php](src/Domain/Components/Middleware/ValidateTokenStructureMiddleware.php)
- [ValidateTokenUserMiddleware.php](src/Domain/Components/Middleware/ValidateTokenUserMiddleware.php)



**NotFoundMiddleware**

Checks if the route has been matched. If not, it will return a `Resource Not Found` payload


**HealthMiddleware**

Invoked when the `/health` endpoint is called and returns a `OK` payload

**ValidateTokenPresenceMiddleware**

Checks if a JWT token is present in the `Authorization` header. If not, an error is returned

**ValidateTokenStructureMiddleware**

Gets the JWT token and checks if it can be parsed. If not, an error is returned

**ValidateTokenUserMiddleware**

Gets the userId from the JWT token, along with other information, and tries to match it with a user in the database. If the user is not found, an error is returned

**ValidateTokenClaimsMiddleware**

Checks all the claims of the JWT token to ensure that it validates. For instance, this checks the token validity (expired, not before), the claims, etc. If a validation error happens, then an error is returned.

**ValidateTokenRevokedMiddleware**

Checks if the token has been revoked. If it has, an error is returned


## Responder
The responder is responsible for constructing the response with the desired output, and emitting it back to the caller. For the moment we have only implemented a JSON response with a specified array as the payload to be sent back. 

The responder receives the outcome of the Domain, by means of a `Payload` object. The object contains all the data necessary to inject in the response.

### Response payload

The application responds always with a specific JSON payload. The payload contains the following nodes:
- `data` - contains any data that are returned back (can be empty)
- `errors` - contains any errors occurred (can be empty)
- `meta` - array of information regarding the payload
  - `code` - the application code returned
  - `hash` - a `sha1` hash of the `data`, `errors` and timestamp
  - `message` - `success` or `error`
  - `timestamp` - the time in UTC format


[adr_pattern]: https://github.com/pmjones/adr
[pds_skeleton]: https://github.com/php-pds/skeleton
