# Deploy

El runbook de infraestructura, provisioning y operaciones vive en el repo separado [`cloud-infra`](https://github.com/your-org/cloud-infra) bajo `helmreel/docs/`.

## Resumen rápido

- **Imagen base** (`php-base:php-8.5-vN`): se construye y publica desde `cloud-infra`. Helmreel la consume como `FROM` en su Dockerfile.
- **Imagen de aplicación** (`backend:<sha>`): la construye `.github/workflows/deploy.yml` de este repo en cada push a `master` y la pushea a Artifact Registry.
- **Deploy a la VM**: el mismo workflow hace SSH a `helmreel-vm` (en GCE) y ejecuta `docker compose up -d` con la imagen recién pusheada.

## ¿Dónde está cada cosa?

| Acción | Repo | Ubicación |
|---|---|---|
| Crear AR repo y SAs de GCP | `cloud-infra` | `helmreel/scripts/01-create-ar-repo.sh`, `02-create-sas.sh` |
| Instalar Docker en la VM | `cloud-infra` | `helmreel/scripts/03-bootstrap-vm.sh` |
| Construir imagen base PHP | `cloud-infra` | `helmreel/docker/php-base/build.sh` |
| Configurar GitHub Secrets de este repo | `cloud-infra` | `helmreel/docs/02-github-secrets.md` |
| Crear `.env.prod` cifrado | `helmreel` (este) | `docker/prod/.env.prod` (no en git en claro) |
| Build + push imagen de app | `helmreel` (este) | `.github/workflows/deploy.yml` |
| Operación (rollback, logs, editar env) | `cloud-infra` | `helmreel/docs/05-operations.md` |

## Primera vez

Sigue en orden los docs de `cloud-infra/helmreel/docs/`:
1. `00-overview.md` — arquitectura.
2. `01-gcp-setup.md` — AR + SAs.
3. `02-github-secrets.md` — secrets de este repo.
4. `03-vm-bootstrap.md` — Docker en la VM.
5. `04-build-base-image.md` — publicar `php-base`.
6. `05-operations.md` — operación diaria.

## Crear `.env.prod` cifrado (este repo)

```bash
cp docker/prod/.env.template docker/prod/.env.prod
$EDITOR docker/prod/.env.prod
ansible-vault encrypt docker/prod/.env.prod
git add docker/prod/.env.prod
git commit -m "ci: prod env vault"
```
