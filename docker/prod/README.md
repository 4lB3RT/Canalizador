# Producción (Cloud)

Stack que se despliega en la VM de Compute Engine. Distribuido por GitHub Actions
(workflow `.github/workflows/deploy.yml`) en cada push a `master`.

## Archivos

| Fichero          | Qué es                                                                      |
|------------------|-----------------------------------------------------------------------------|
| `docker-compose.yml` | Stack de prod (nginx, php, worker, mysql, redis, rabbitmq).             |
| `nginx.conf`     | Versión con TLS — se activa cuando hay dominio. Set `NGINX_CONF_FILE=./nginx.conf` en `.env.prod`. |
| `nginx.no-tls.conf` | Versión pre-dominio: sirve solo HTTP en :80. Default mientras no haya TLS. |
| `.env.template`  | Plantilla con todas las variables de entorno. No se sube cifrada.            |
| `.env.prod`      | `.env` real cifrado con **Ansible Vault**. Único que va al repo.            |

En la VM nunca vive un clon del repo. GitHub Actions hace `scp` de estos
ficheros a `/opt/canalizador/docker/prod/`, descifra `.env.prod` y ejecuta
los comandos de deploy inline vía SSH (`docker pull`, `docker compose up`,
`artisan migrate`, etc.).

Cuando exista el SPA, el mismo workflow copiará `dist.tar.gz` a
`/opt/canalizador/web`.

## Crear `.env.prod` por primera vez

```bash
# 1. Copiar la plantilla y rellenar valores reales
cp docker/prod/.env.template docker/prod/.env.prod
$EDITOR docker/prod/.env.prod

# 2. Cifrar (te pedirá la passphrase — guárdala en GitHub Secrets como ANSIBLE_VAULT_PASSWORD)
ansible-vault encrypt docker/prod/.env.prod

# 3. Commit
git add docker/prod/.env.prod
git commit -m "ci: prod env vault"
```

## Editar `.env.prod`

```bash
ansible-vault edit docker/prod/.env.prod   # te pedirá la passphrase
git commit -am "ci: update prod env"
```

## Ver `.env.prod` descifrado puntualmente

```bash
ansible-vault view docker/prod/.env.prod
```

## Cambiar la passphrase

```bash
ansible-vault rekey docker/prod/.env.prod
# después actualizar el secret ANSIBLE_VAULT_PASSWORD en GitHub
```
