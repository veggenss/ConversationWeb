
# Chat på Nett
<img src="https://github.com/user-attachments/assets/ce1fb91e-5326-441f-ad19-29a8d6192546" width="300" height="300">

## Nydelig design

<img src="https://github.com/user-attachments/assets/527c6362-8ad5-40d2-9b1b-11c3fd0b15a2" width="800" height="450">

### Bedre enn Discord ✅

## Veldig sikkert
### Dataen din blir ikke solgt 😀
<!--(her skal det være et bilde av et eller annet... sikkert noe som det i Ord på Nett readme-en, for eksempel den låsen)-->

## Ytringfrihet ✅✅✅
### Si hva en du vil! (så lenge det ikke er noe negativt om Ord på Nett and assoc.)
<!--(her skal det være et bilde av en mann som snakker eller noe, idk)-->

<br> <br>

### For utvikling:
**Hva trenger jeg for å kjøre dette lokalt?**
Du må ha [PHP](https://www.php.net) installert med [MySQLIi](https://www.php.net/manual/en/mysqli.installation.php) extension enablet, [MYSQL](https://www.mysql.com/)/[MariaDB](https://mariadb.org/) en webserver (f.eks [Apache](https://httpd.apache.org/) eller [Nginx](https://nginx.org)) som faktisk hoster alt, og [Composer](https://getcomposer.org) for å installere: [PHPMailer](https://github.com/PHPMailer/PHPMailer), [PHPdotenv](https://github.com/vlucas/phpdotenv) og [Ratchet](https://github.com/ratchetphp/Ratchet)
<br> <br>

**Hvordan setter jeg opp databasen?** Last ned [denne sql filen](https://github.com/user-attachments/files/22944164/conversationWeb.sql) - det er en eksportering av databasen vi bruker i Samtaler på Nett. Jeg skal prøve mitt beste å holde denne SQL filen så oppdatert som mulig. Hvis det er noe som ikke funker, altså, den er utdatert, vennligst kontakt [@IsakBH](https://www.github.com/IsakBH). For å faktisk lage databasen og sånn, gå inn i mariadb/mysql og skriv:
```sql
CREATE DATABASE conversationWeb;
```
Det oppretter en ny database som heter 'conversationWeb'.
For å importere fra .sql filen du lastet ned tidligere:
```bash
mysql -u dittBrukernavnHer -p conversationWeb < pathTilSqlFilen
```
Den importerer dataen fra .sql filen du lastet ned og setter det inn i databasen 'conversationWeb'.

For å gi brukeren din tilgang til å reade og write til databasen, går du inn i MariaDB/MySQL monitor og skriver:
```sql
GRANT ALL PRIVILEGES ON conversationWeb.* TO 'dittBrukernavnHer'@'localhost';
```
Den gir alle privileges/permissions på databasen conversationWeb til din bruker.
<br>

#### Step 1: Les det over dette her og se om du har det du trenger
#### Step 2: Clone repo-et
#### Step 3: Flytt mappen inn i document root-en til webserveren din slik at du kan se på det på localhost :D
#### Step 4: PLEASE skriv god kode, gode kommentarer og gode commit meldinger

