# Melis Commerce Order Invoice

Offers generation of invoice functionalities to the Melis Commerce Module

## Getting started

These instructions will get you a copy of the project up and running on your machine.

### Prerequisites

The following modules need to be installed to have Melis Commerce Order Invoice module run:
* Melis core
* Melis commerce
 
### Installing

This project can be installed using Composer. Add the following to your
composer.json:

```javascript
    {
        "require": {
            "melisplatform/melis-commerce-order-invoice": "^3.0"
        },
        "repositories": [
            {
                "type": "git",
                "url": "https://github.com/melisplatform/melis-commerce-order-invoice"
            }
        ]
    }
```

Run the composer command:
```
composer require melisplatform/melis-commerce-order-invoice
```

### Database    

Database model is accessible via the MySQL Workbench file:  
```
/melis-commerce-order-invoice/install/sql/model
```  
Database will be installed through composer and its hooks.  
In case of problems, SQL files are located here:  
```
/melis-commerce-order-invoice/install/sql  
```

## Tools and elements provided
* Order Invoice Tab inside the Melis Commerce Orders tool
  - Auto generation of invoice for an order
  - regenerate an invoice for an order
  - download latest or specific invoice for an order
 
## Authors

* **Melis Technology** - [www.melistechnology.com](https://www.melistechnology.com/)

See also the list of [contributors](https://github.com/melisplatform/melis-commerce-order-invoice/contributors) who participated in this project.


## License

This project is licensed under the OSL-3.0 License - see the [LICENSE.md](LICENSE.md) file for details