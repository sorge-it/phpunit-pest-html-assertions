# VHS records the terminal; PHP and Composer run the demo app inside it. Composer comes from a stage of
# its own, so Dependabot updates its digest too.
FROM composer:2.11.1@sha256:8de2184c6de224f4689e8f80ed5f5edaf0030b6b8b7839a22a2bcb6722ac87d0 AS composer

FROM ghcr.io/charmbracelet/vhs:v0.12.1@sha256:ea49a6a1c529be83153e88321892b5585964418f0b9055e8c1e0d732194234a3

RUN apt-get update \
 && apt-get install -y --no-install-recommends php-cli php-xml php-mbstring unzip git \
 && rm -rf /var/lib/apt/lists/*

COPY --from=composer /usr/bin/composer /usr/local/bin/composer

ENTRYPOINT []
