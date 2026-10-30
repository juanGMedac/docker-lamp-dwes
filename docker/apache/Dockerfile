FROM httpd:2.4

# Activa los módulos necesarios para delegar PHP en PHP-FPM y para .htaccess
RUN sed -i \
        -e 's/^#\(LoadModule proxy_module \)/\1/' \
        -e 's/^#\(LoadModule proxy_fcgi_module \)/\1/' \
        -e 's/^#\(LoadModule rewrite_module \)/\1/' \
        /usr/local/apache2/conf/httpd.conf \
    && echo 'Include conf/extra/dwes.conf' >> /usr/local/apache2/conf/httpd.conf

COPY dwes.conf /usr/local/apache2/conf/extra/dwes.conf
