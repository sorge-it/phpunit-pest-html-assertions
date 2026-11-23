# VHS records the terminal; PHP and Composer run the demo app inside it. Composer comes from a stage of
# its own, so Dependabot updates its digest too.
FROM composer:2.10.3@sha256:2cf275428757e5435a667bfc0d038b14520ca02be2b1b70d44a05c8c19e2de48 AS composer

FROM ghcr.io/charmbracelet/vhs:v0.12.1@sha256:ea49a6a1c529be83153e88321892b5585964418f0b9055e8c1e0d732194234a3

RUN apt-get update \
 && apt-get install -y --no-install-recommends php-cli php-xml php-mbstring unzip git \
 && rm -rf /var/lib/apt/lists/*

COPY --from=composer /usr/bin/composer /usr/local/bin/composer

ENTRYPOINT []
