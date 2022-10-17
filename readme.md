# Installation

Run following commands in project's main dir:
```
docker-compose up
docker-compose exec php bash 
composer install
```

# Running code

To run project:
```
docker-compose exec php bash
php script.php sample.csv
```

# CoinGecko API

Free version of gecko API allows for 10 to 50 request per minute. 
To be on safer side there is sleep command for 6s after every API call