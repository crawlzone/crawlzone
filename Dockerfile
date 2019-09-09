FROM node:latest

# Install Puppeteer dependencies

RUN apt-get update && \
apt-get install -yq gconf-service libasound2 libatk1.0-0 libc6 libcairo2 libcups2 libdbus-1-3 \
libexpat1 libfontconfig1 libgcc1 libgconf-2-4 libgdk-pixbuf2.0-0 libglib2.0-0 libgtk-3-0 libnspr4 \
libpango-1.0-0 libpangocairo-1.0-0 libstdc++6 libx11-6 libx11-xcb1 libxcb1 libxcomposite1 \
libxcursor1 libxdamage1 libxext6 libxfixes3 libxi6 libxrandr2 libxrender1 libxss1 libxtst6 \
fonts-ipafont-gothic fonts-wqy-zenhei fonts-thai-tlwg fonts-kacst ttf-freefont \
ca-certificates fonts-liberation libappindicator1 libnss3 lsb-release xdg-utils wget && \
wget https://github.com/Yelp/dumb-init/releases/download/v1.2.1/dumb-init_1.2.1_amd64.deb && dpkg -i dumb-init_*.deb

### Install PHP CLI https://linuxize.com/post/how-to-install-php-on-debian-9/

RUN apt -y install lsb-release apt-transport-https ca-certificates && \
    wget -O /etc/apt/trusted.gpg.d/php.gpg https://packages.sury.org/php/apt.gpg && \
    echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" | tee /etc/apt/sources.list.d/php7.3.list && \
    apt update && apt -y install php7.3-common php7.3-xml php7.3-sqlite3 php7.3-cli php7.3-curl php7.3-mbstring

### Install composer

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" && \
    php -r "if (hash_file('sha384', 'composer-setup.php') === 'a5c698ffe4b8e849a443b120cd5ba38043260d5c4023dbf93e1558871f1f07f58274fc6f4c93bcfd858c6bd0775cd8d1') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;" && \
    php composer-setup.php && \
    php -r "unlink('composer-setup.php');" && \
    mv /composer.phar /usr/local/bin/composer

# Clean up
# RUN rm -f dumb-init_*.deb && apt-get clean && apt-get autoremove -y && rm -rf /var/lib/apt/lists/*

RUN yarn global add puppeteer@latest && yarn cache clean

ENV NODE_PATH="/usr/local/share/.config/yarn/global/node_modules:${NODE_PATH}"

# ENV PATH="/tools:${PATH}"

RUN groupadd -r pptruser && useradd -r -g pptruser -G audio,video pptruser

#COPY --chown=pptruser:pptruser ./tools /tools

# Set language to UTF8
ENV LANG="C.UTF-8"

WORKDIR /app

# Add user so we don't need --no-sandbox.
#RUN mkdir /screenshots \
#	&& mkdir -p /home/pptruser/Downloads \
#    && chown -R pptruser:pptruser /home/pptruser \
#    && chown -R pptruser:pptruser /usr/local/share/.config/yarn/global/node_modules \
#    && chown -R pptruser:pptruser /screenshots \
#    && chown -R pptruser:pptruser /app \
#    && chown -R pptruser:pptruser /tools

# Run everything after as non-privileged user.
#USER pptruser

# --cap-add=SYS_ADMIN
# https://docs.docker.com/engine/reference/run/#additional-groups

ENTRYPOINT ["dumb-init", "--"]

# CMD ["/usr/local/share/.config/yarn/global/node_modules/puppeteer/.local-chromium/linux-526987/chrome-linux/chrome"]

#CMD ["node", "index.js"]

#FROM php:7.1-cli

