# Pimcore Services
Useful Services for pimcore

***Installation via Composer***
```
composer require putzflorian/pimcore-services
```

***Add to your parameters.yml***
```yaml
parameters:
    putzflorian_pimcoreservices:
        safe_crypt:
            secret_key: ##secret_key##
            secret_iv: ##secret_iv##
```

***Register as a Service in your services.yml***
```yaml
services:
    Putzflorian\PimcoreServices:
```

***Use it in your Controller:***
```php
public function testAction(Request $request, \Putzflorian\PimcoreServices $pimcoreServices){}
```