CMD /c composer update
CMD /c npm install
CMD /c npm audit fix
CMD /c php artisan key:generate
CMD /c npm run prod