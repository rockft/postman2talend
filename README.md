# postman2talend

Convert postman v1 collection Export json to Talend API Tester.

## Usage

### Basic Usage

```usage
php postman2talend.php [postman.json]
```

> The default import file name is postman.json
> Output file name is postman2talend.json

only a single postman collection can be converted.
`Request` `Header` `Parameter` conversion completed, `Body` is unavailable.

> PHP >= 7.0
