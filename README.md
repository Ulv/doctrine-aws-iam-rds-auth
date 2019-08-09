# Usage

The goal is to authenticate users in RDS PostgreSQL through AWS IAM (using EC2 instance role policy)

## Prepare everything in AWS (IAM, RDS)

* Create user in RDS
```sql
CREATE USER userdbpci WITH LOGIN;
GRANT rds_iam TO admindbpci;
```
* Grant privileges to new user on database
```sql
GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA public to userdbpci;
```
* Create IAM policy to auth user in RDS through
```json
{
    "Version": "2012-10-17",
    "Statement": [
        {
            "Effect": "Allow",
            "Action": [
                "rds-db:connect"
            ],
            "Resource": [
                "arn:aws:rds-db:eu-west-1:236183847031:dbuser:db-P0SWXGYT54Q2ADPJJVWQGKL35U/userdbpci"
            ]
        }
    ]
}
```
* Attach policy to EC2 instance (or role, which is used by EC2 instance)
* Test connection from EC2 console
```bash
#!/bin/bash

HOST="6fpewskuvldmv0.y3fhb4t2yxc4.eu-west-1.rds.amazonaws.com"
PASSWORD="$(aws rds generate-db-auth-token --hostname $HOST --port 5432 --region eu-west-1 --username userdbpci )"
CONNSTRING="host=$HOST port=5432 sslmode=verify-full sslrootcert=/data/www/rds-combined-ca-bundle.pem dbname=paymentDB user=userdbpci password=$PASSWORD"
psql "$CONNSTRING"
```
* Use default instance aws credentials from php

## Configure doctrine's environment variables in symfony

```ini
# Doctrine database connection
# Note: do not specify DATABASE_URL, as it overrides this values
# Note 2: if using SSL with RDS Doctrine must be configured to use SSL also
DATABASE_DRIVER=pdo_pgsql
DATABASE_USER=
DATABASE_HOST=
DATABASE_PORT=5432
DATABASE_NAME=
DATABASE_PASSWORD=
DATABASE_VERSION=9.5

# Authenticate user via IAM AWS service instead of password? int
# PS.: see AWS settings in its own configuration block
# Available values: 0, 1
DATABASE_USE_IAM=1

# Username to query IAM, string
IAM_USERNAME=userdbpci

# Use token cache? int
# Available values: 0, 1
IAM_USE_TOKEN_CACHE=1

# IAM token cache time in minutes, int
IAM_TOKEN_CACHE_TIME_MINUTES=10
```

## SSL configuration in RDS/Doctrine

* Enable SSL on RDS database (in AWS console)
* Download SSL certificate from [the official link](https://s3.amazonaws.com/rds-downloads/rds-combined-ca-bundle.pem)
* Enable SSL in doctrine
```yaml
parameters:
    env(SSL_ROOT_CERT): '/path/to/rds-combined-ca-bundle.pem'
    db_ssl_cert: '%env(SSL_ROOT_CERT)%'
    # ....
doctrine:
    dbal:
        connections:
            default:
                sslmode: 'verify-full'
                sslrootcert: '%db_ssl_cert%'
    # ....
```

# Useful links

* [IAM Database Authentication for MySQL and PostgreSQL](https://docs.aws.amazon.com/AmazonRDS/latest/UserGuide/UsingWithRDS.IAMDBAuth.html)
* [The very useful gist](https://gist.github.com/sators/38dbe25f655f1c783cb2c49e9873d58a)
* [Using SSL with a PostgreSQL DB Instance ](https://docs.aws.amazon.com/AmazonRDS/latest/UserGuide/CHAP_PostgreSQL.html#PostgreSQL.Concepts.General.SSL)
* [Managing PostgreSQL users and roles](https://aws.amazon.com/ru/blogs/database/managing-postgresql-users-and-roles/)

