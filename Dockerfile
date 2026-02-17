# Usiamo l'immagine ufficiale di PHP con Apache
FROM php:8.2-apache

# 1. Aggiorniamo il sistema e installiamo msmtp
# msmtp: il client che invia le mail
# msmtp-mta: crea automaticamente il collegamento finto per "sendmail"
RUN apt-get update && apt-get install -y \
    msmtp \
    msmtp-mta \
    && rm -rf /var/lib/apt/lists/*

# 2. Installiamo le estensioni PHP per il database
# mysqli: quello che stai usando nel tuo codice
# pdo_mysql: utile se vorrai modernizzare il codice in futuro
RUN docker-php-ext-install mysqli pdo pdo_mysql

# 3. Configuriamo msmtp per parlare con Mailpit
# Creiamo il file di configurazione direttamente dentro l'immagine
RUN echo "defaults" > /etc/msmtprc \
    && echo "auth off" >> /etc/msmtprc \
    && echo "tls off" >> /etc/msmtprc \
    && echo "host mailpit" >> /etc/msmtprc \
    && echo "port 1025" >> /etc/msmtprc \
    && echo "logfile /var/log/msmtp.log" >> /etc/msmtprc \
    && echo "account default" >> /etc/msmtprc \
    && echo "from noreply@bibliotech.it" >> /etc/msmtprc

# 4. Attiviamo il modulo rewrite di Apache (utile per URL puliti)
RUN a2enmod rewrite

# 5. Impostiamo la cartella di lavoro
WORKDIR /var/www/html

# I permessi vengono gestiti solitamente da Apache, ma questo aiuta
RUN chown -R www-data:www-data /var/www/html