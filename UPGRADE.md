# Upgrade

## 0.8
Blocks are now adjustable in the project configuration. To copy the configuration run the following command:

```bash
bin/console webspace:settings:copy-config
```

## 0.7
If you upgrade from version 0.6 to 0.7, you need to run the following command to update the database schema:

```bash
bin/console webspace:settings:json-migrate
```