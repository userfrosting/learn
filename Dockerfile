FROM bitnami/minideb:bullseye

RUN \
  ## Docker User
  useradd -u 911 -U -d /var/www -s /bin/false xyz && \
  usermod -G users xyz && \
  ## Install Pre-reqs
  install_packages \
    apt-transport-https \
    ca-certificates \
    curl \
    lsb-release \
    nginx \
    unzip \
    cron \
    git \
    wget && \
  ## Install PHP APT Repository
  wget -O /etc/apt/trusted.gpg.d/php.gpg https://packages.sury.org/php/apt.gpg && \
  echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" | tee /etc/apt/sources.list.d/php8.2.list && \
  ## Install PHP 8.2
  install_packages \
    php8.2 \
    php8.2-fpm \
    php8.2-gd \
    php8.2-curl \
    php8.2-zip \
    php8.2-mbstring \
    php8.2-xml \
    php8.2-intl

RUN \
  ## Download GRAV
  mkdir -p /grav && \
  GRAV_VERSION=$(curl -sX GET "https://api.github.com/repos/getgrav/grav/releases/latest" | awk '/tag_name/{print $4;exit}' FS='[""]') && \
  curl -o /grav/grav.zip -L https://github.com/getgrav/grav/releases/download/${GRAV_VERSION}/grav-v${GRAV_VERSION}.zip && \
  unzip /grav/grav.zip -d /var/www && \
  rm /grav/grav.zip && \
  rm -R /var/www/html && \
  ## Setup cron
  touch /var/spool/cron/crontabs/xyz && \
  (crontab -l; echo "* * * * * cd /var/www/grav;/usr/bin/php bin/grav scheduler 1>> /dev/null 2>&1") | crontab -u xyz - && \
  ## Nginx Logs
  ln -sf /dev/stdout /var/log/nginx/access.log && \
  ln -sf /dev/stderr /var/log/nginx/error.log

EXPOSE 80 443

COPY Docker/ /

RUN chmod +x /init

WORKDIR /var/www/grav

CMD ["/init"]