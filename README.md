## Introducción al proyecto

El trabajo diseña y construye un laboratorio DevSecOps en el que cada cambio de código pasa por una fase de seguridad automatizada (el *Gate*) antes de llegar a un clúster Kubernetes, y en el que el estado del clúster se audita de forma continua e independiente del pipeline.

## Qué contiene este repositorio

Este repositorio publica las **dos aplicaciones sobre las que se probó el Gate**. Cada una es un proyecto independiente con su propio `.gitlab-ci.yml`, que en el laboratorio vive en su propio repositorio de GitLab.

```
.
├── pipeline-gate/          Aplicación mínima de demostración del Gate simulando secretos de AWS.
└── fantasy-league-ctf/     Aplicación PHP realista, desplegada por GitOps que utilizo en un lab privado en el MCSEH de LaSalle.
```

### `pipeline-gate/`

Aplicación mínima (una imagen Alpine con un script) pensada para probar el Gate con el menor ruido posible. Es el caso de uso de detección de secreto filtrado, en este caso credenciales de AWS simuladas.

Etapas del pipeline: `secrets-scan`, `sast`, `dependency-scan`, `manifest-lint`, `policy-check`, `build` (sin publicar la imagen) e `incident-report`.

### `fantasy-league-ctf/`

Aplicación PHP con base de datos SQLite que se despliega de verdad en el clúster.

- `src/`: código de la aplicación y esquema de datos (`init.sql`).
- `Dockerfile` y `apache-config.conf`: imagen basada en `php:8.1-apache`.
- `policy.yaml`: política de Kyverno que se evalúa en el pipeline.
- `k8s/`: manifiestos de Kubernetes y definiciones de Argo CD, con una carpeta por entorno (`k8s/` para PROD, `k8s/qa/` y `k8s/dev/`).

Etapas del pipeline: las mismas que `pipeline-gate`, más `update-manifest` (reescribe el manifiesto de su rama con la imagen `<rama>-<commit>`) y `dast` (OWASP ZAP contra la aplicación desplegada).

Cada rama corresponde a un entorno (`dev`, `qa` y `main`), y cada aplicación de Argo CD vigila su rama y su carpeta.

## Contenido intencionadamente inseguro

Estas dos aplicaciones **se diseñaron para tener fallos**, porque su objetivo es comprobar que el Gate los detecta. No son descuidos y no deben reutilizarse como código de producción.

- `fantasy-league-ctf` es una aplicación CTF vulnerable a propósito (por ejemplo, un token de acceso fijo en `src/get_flag.php` y usuarios con contraseñas de ejemplo en `src/init.sql`). El job `sast` falla con hallazgos de Semgrep sobre este código, y eso es el resultado esperado.
- `pipeline-gate/config.yaml` se usó para insertar un secreto simulado y comprobar que `secrets-scan` detiene el pipeline. En la versión publicada, las credenciales son variables de entorno sin valor.
- Las credenciales de ejemplo de `init.sql` pertenecen a datos de demostración y no dan acceso a ningún sistema real.

## Ejecución

Los pipelines están pensados para el laboratorio descrito en la memoria (GitLab CE, GitLab Runner con ejecutor Kubernetes, registro interno de MicroK8s y Argo CD). **No se pueden ejecutar tal cual fuera de ese entorno.**


## Uso de inteligencia artificial

Las aplicaciones `fantasy-league-ctf` y `pipeline-gate` se crearon con apoyo de IA generativa y se revisó y adaptó después para este laboratorio. La aplicación `fantasy-league-ctf` fue creada previamente a este trabajo con fines educativos. La IA ha sido dirigida por mí para crear los entornos k8s de manera correcta y ha ayudado a debugar errores posteriores de implementación.
