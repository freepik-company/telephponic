## 4.0.0 (2024-04-03)

### Feat

- add metric handling (#16)

## 3.0.3 (2023-10-31)

### Fix

- **Curl.php**: fix error tracking curl_init without any parameters passed

## 3.0.2 (2023-10-19)

### Fix

- **gRPC-integration**: remove a space character from methods name (#12)

## 3.0.1 (2023-10-18)

### Fix

- **Telephponic**: modify the way current context is fetched and used to avoid error for null context when shutdown (#11)

## 3.0.0 (2023-10-17)

### Feat

- add support to enable stacktrace as trace attribute (#7)
- **integrations**: switch default value for trace flags from `false` to `true`
- **Builder**: update type withXxxIntegration to provided trace flags as true by default

This is no a BC level change, but it can completely change the behaviour of your current setup, so be careful.
- **integrations/PDO**: add pdo or pdoStatement instance id to all traces related to (#9)

### Fix

- **Builder**: remove some methods that triggers error for dynamic property declaration (#6)

## 2.1.0 (2023-10-03)

## 2.2.0 (2023-10-05)

### Feat

- **Integrations**: make integrations optional (#5)

## 2.1.0 (2023-10-05)

### Feat

- **Integrations**: make integrations optional (#5)
- **integration/pdo**: add support to autowatch PdoStatement::bindParam function (#4)

## 2.0.0 (2023-10-03)

### Feat

- avoid spans and context to be detached twice

## 1.2.0 (2023-09-27)

### Feat

- **GuzzlePromises**: downgrade to ^1.0 version of Promises

## 1.1.0 (2023-08-31)

### Feat

- **VT-160**: improve probability sampling configuration (#2)

## 1.0.0 (2023-08-30)

### Refactor

- new major version

## 0.4.0 (2023-08-30)

### BREAKING CHANGE

- PHP min version is now 8.1

### Feat

- improve integration tracing info

## 0.3.0 (2023-08-25)

### Feat

- **Dockerfile**: add fc-image support

## 0.2.0 (2023-07-21)

### Feat

- add support to append integrations to telephonic
- add support for adding attributes to a span by its identifier name

## 0.1.0 (2023-07-20)

### Feat

- Initial version

### Refactor

- set namespace according to company code style
