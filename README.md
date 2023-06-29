<a href="https://scrutinizer-ci.com/g/asili2001/MVC/">
    <img src="https://scrutinizer-ci.com/g/asili2001/MVC/badges/quality-score.png?b=main">
</a>
<a href="https://scrutinizer-ci.com/g/asili2001/MVC/">
    <img src="https://scrutinizer-ci.com/g/asili2001/MVC/badges/coverage.png?b=main">
</a>
<a href="https://scrutinizer-ci.com/g/asili2001/MVC/inspections/5abb96bb-9f61-4a7a-b888-aae4257e4887/log">
    <img src="https://scrutinizer-ci.com/g/asili2001/MVC/badges/build.png?b=main">
</a>
<a href="https://scrutinizer-ci.com/code-intelligence">
    <img src="https://scrutinizer-ci.com/g/asili2001/MVC/badges/code-intelligence.svg?b=main">
</a>


<h2>Om MVC</h2>
MVC är en kurs som ingår i webbprogrammeringsprogrammet på Blekinge tekniska högskola (BTH), som sträcker sig över 180 högskolepoäng. Kursen fokuserar på att lära sig programmera på ett objektorienterat sätt och att använda MVC-strukturen (Model-View-Controller) i vår kod. Inom kursen använder vi PHP som programmeringsspråk och specifikt Symfony-frameworket.

<h2>Klona och starta igång webbplatsen</h2>
<ul>
<li>
    Installera Git: Om du inte redan har Git installerat på din dator måste du först ladda ner och <a href="https://git-scm.com/book/en/v2/Getting-Started-Installing-Git">installera</a> det.
</li>
<li>
    Skapa en mapp: Skapa en tom mapp på din dator där du vill ha kursens projekt.
</li>
<li>
    Klona projektet: Öppna terminalen eller kommandotolken och navigera till den mapp där du vill klona projektet. Använd kommandot cd för att byta till rätt mapp.<br/>
    För att klona projektet från GitHub använder du följande kommando:
    
    git clone https://github.com/asili2001/MVC.git

</li>
<li>
    Installera beroenden: När kloningen är klar, navigera in i den klonade projektmappen med cd projektmappens namn <br/>
    Kör följande kommando för att installera beroenden via Composer:
    
    composer install

</li>
<li>
    Skapa databasen: I kursen använder vi oss av Doctrine som är ett databas ORM. <br/>
    För att skapa databasen kör följande komando:
    
    php bin/console doctrine:database:create

</li>
<li>
    Kör migrations: För att hämta senaste databas ändringar måste man migrera <br/>
    För att migrera databasen kör följande komando:
    
    php bin/console doctrine:migrations:migrate

</li>
<li>
    Starta igång: För att starta igång måste vi starta serven <br/>
    För att starta servern kör följande komando:
    
    php -S localhost:8888 public/

8888 är porten som servern kör på

</li>
</ul>
