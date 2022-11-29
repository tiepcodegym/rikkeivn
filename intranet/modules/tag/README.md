Tags package
===

Command line
---

1. Run seeder

    ```
    php artisan db:seed
    ```

2. Push data update into nodejs/elasticsearch server

    ```
    php artisan elastic:push
    ```

3. Export data update of mysql into file

    ```
    php artisan elastic:push --export
    ```

4. export all data to file
    
    ```
    php artisan elastic:push --export --all
    ```

After export file .json, use cmd to import into elasticsearch
    
    ```
    curl -s -XPOST 'http://127.0.0.1:9200/_bulk' --data-binary @elastic_data.json
    ```