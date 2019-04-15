=== WooCommerce SuperFaktura ===
Contributors: webikon, johnnypea, savione, kravco, superfaktura
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=ZQDNE7TP3XT36
Tags: superfaktura, invoice, faktura, proforma, woocommerce
Requires at least: 4.4
Tested up to: 5.1
Stable tag: 1.8.15
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Connect your WooCommerce eShop with online invoicing system SuperFaktura.

== Description ==

SuperFaktura extension for WooCommerce enables you to create invoices using third-party online app SuperFaktura.

SuperFaktura is an online invoicing system for small business owners available in Slovakia ([superfaktura.sk](http://www.superfaktura.sk/)) and Czech Republic ([superfaktura.cz](http://www.superfaktura.cz/)).

Main features of WooCommerce Superfaktura include:

* Automatically create invoices in SuperFaktura.
* Add fields for invoice details to WooCommerce Checkout form.
* Link to the invoice is added to
	* Customer notification email sent by WooCommerce
	* Order detail
	* WooCommerce My Account page
* Set your own rules, when proforma or real invoice should be generated. Want to send proforma invoice on order creation and real invoice after payment? We got that covered.
* Custom invoice numbering.

This plugin is not directly associated with superfaktura.sk, s.r.o. or with superfaktura cz, s.r.o. or oficially supported by their developers.

Created by [Ján Bočínec](http://bocinec.sk/) with the support of [Slovak WordPress community](http://wp.sk/) and [WordPress agency Webikon](http://www.webikon.sk/).

For priority support and more Woocommerce extensions (payment gateways, invoicing…) check [PlatobneBrany.sk](http://platobnebrany.sk/)

== Installation ==

1. Upload the entire SuperFaktura folder *woocommerce-superfaktura* to the /wp-content/plugins/ directory (or use WordPress native installer in Plugins -> Add New Plugin). And activate the plugin through the 'Plugins' menu in WordPress.
2. Visit your SuperFaktura account and get an API key
3. Set your SuperFaktura Account Email and API key in *WooCommerce -> Settings -> SuperFaktura*

== Screenshots ==
Coming soon.

== Frequently Asked Questions ==

= Invoice is not created automatically =

Check the settings in *WooCommerce -> Settings -> SuperFaktura*
You should fill your Account Email, API key and set the Order status in which you would like to create the invoice.

= Invoice is marked as paid =

Status of the payment is related to Order status. When an invoice is created with the status “On-Hold”, it will not be marked as paid. When an invoice is created with the status “Completed”, it will be marked as paid.

= The plugin stopped working and I don’t know why! =

This usually happens when you change your login email address. The email address in *WooCommerce -> Settings -> SuperFaktura* must be the same as the one you use to log in to SuperFaktura.

= Where can I find more information about SuperFaktura API? =

You can read more about SuperFaktura API integration at [superfaktura.sk/api](http://www.superfaktura.sk/api/)

== Changelog ==

= 1.8.15 =
* Opravená chyba v nastaveniach vystavenia faktúry

= 1.8.14 =
* Opravený chýbajúci popis produktu v položke faktúry

= 1.8.13 =
* Nahradené volania deprecated funkcií, doplnená značka [YEAR_SHORT] v číslovaní faktúr

= 1.8.12 =
* Doplnená možnosť nastaviť adresu webu v pätičke faktúry

= 1.8.11 =
* Doplnená možnosť aktualizovať pri vystavení faktúry údaje klienta v SuperFaktúre

= 1.8.10 =
* Doplnená možnosť vypnúť zobrazovanie kódu kupónu v popise

= 1.8.9 =
* Opravená chyba v implementácii nastavení pluginu

= 1.8.8 =
* Pridané nastavenie vypnúť/zapnúť možnosť fakturácie na firmu

= 1.8.7 =
* Doplnený autor pluginu

= 1.8.6 =
* Doplnená možnosť nastaviť jazyk faktúry podľa WPML jazyka objednávky
* Doplnená možnosť vypnúť zobrazovanie zľavy na produkt v popise

= 1.8.5 =
* Opravená chyba vo vystavovaní faktúry

= 1.8.4 =
* Opravená chyba v popise produktu

= 1.8.3 =
* Doplnené nastavenia pre stav úhrady faktúry

= 1.8.2 =
* Doplnená možnosť pridať poznámku k objednávke do poznámky na faktúre

= 1.8.1 =
* Doplnená kompatibilita s pluginom N-Media WooCommerce PPOM

= 1.8.0 =
* Rozdelené nastavenia do logických celkov

= 1.7.10 =
* Doplnená možnosť vypnúť automatickú úhradu faktúry pre vybavené objednávky

= 1.7.9 =
* Zobrazenie poznámky o prenesení daňovej povinnosti len pri zadanom VAT ID

= 1.7.8 =
* Opravená chyba pri vyskladávaní mena v dodacej adrese

= 1.7.7 =
* Doplnená možnosť pregenerovať zálohovú faktúru

= 1.7.6 =
* Opravená chyba pri zaokrúhľovaní ceny položiek faktúry

= 1.7.5 =
* Opravená chyba pri prenášaní spôsobu doručenia do SuperFaktúry

= 1.7.4 =
* Opravený problém so zobrazením dátumu dodania pri editácii faktúry

= 1.7.3 =
* Opravená chyba s overovaním SSL certifikátu

= 1.7.2 =
* Opravené české preklady

= 1.7.1 =
* Pridané spôsoby platby zo SuperFaktúry

= 1.7 =
* Pridaná možnosť ručne vytvoriť zálohovú faktúru a faktúru

= 1.6.49 =
* Aktualizovaná informácia o kompatibilite s najnovšou verziou WordPress

= 1.6.48 =
* Pridaný filter, ktorý umožňuje pridať do faktúry položky navyše

= 1.6.47 =
* Doplnená možnosť vypnúť faktúry na stránke Objednávka prijatá

= 1.6.46 =
* Doplnené slovenské a české preklady v nastaveniach pluginu

= 1.6.45 =
* Doplnená možnosť vypnúť faktúry v emailoch pre objednávky na dobierku

= 1.6.44 =
* Opravené chyby v generovaní prílohy emailu

= 1.6.43 =
* Opravené pregenerovanie faktúry

= 1.6.42 =
* Opravené zaokrúhľovanie v poštovnom a zľave

= 1.6.41 =
* Opravený problém s konektivitou na API meine.superfaktura.at

= 1.6.40 =
* Zmeny v implementácii SF API (identifikácia modulu, nastavenie zaokrúhľovania)

= 1.6.39 =
* Opravenie rozbaľovania firemných údajov v My Account

= 1.6.38 =
* Doplnenie podpory pre meine.superfaktura.at
* Pridaná možnosť vypnúť odkaz na faktúru v emailoch

= 1.6.37 =
* Opravenie chyby so zdvojenou zľavou

= 1.6.36 =
* Pridaný filter, ktorý umožňuje prispôsobiť, kedy sa má faktúra vystaviť ako zaplatená

= 1.6.35 =
* Pridaná možnosť pridať do faktúry položku "Poštovné" aj v prípade, že má nulovú sumu (text je možné nastaviť)

= 1.6.34 =
* Pridaná možnosť nastaviť meno v adrese dodania ako názov spoločnosti spolu s menom a priezviskom

= 1.6.33 =
* Opravené vystavovanie faktúr v stave objednávky "prijatá"

= 1.6.32 =
* Pridaná možnosť posielať PDF faktúry v prílohe emailu

= 1.6.31 =
* Opravené selecty v nastaveniach pluginu

= 1.6.30 =
* Opravené počítanie dane pri nulovej hodnote

= 1.6.29 =
* Pridaná podpora pre plugin Nastavenia SK pre WooCommerce

= 1.6.28 =
* Opravená chyba prejavujúca sa v PHP verziách starších ako 5.5

= 1.6.27 =
* Pridaný konfiguračný súbor pre WPML String Translation

= 1.6.26 =
* Pridaná možnosť nastaviť dátum vytvorenia faktúry rovnaký ako dátum vytvorenia objednávky

= 1.6.25 =
* Opravena kompatibilita s pluginom WooCommerce Order Status Manager

= 1.6.24 =
* Pridaná spätná kompatibilita s WooCommerce 2.6+

= 1.6.23 =
* Opravená kompatibilita s pluginom WooCommerce 3.2.0

= 1.6.22 =
* Pridaná možnosť zapnúť/vypnúť PAY by square QR kód

= 1.6.21 =
* Pridaná možnosť filtrovať posielane informácie o zákazníkovi a objednávke
* Opravené počítanie dane pri zľavnených produktoch

= 1.6.20 =
* Pridaná možnosť nastaviť ID číselníka

= 1.6.19 =
* Doplnené Odberné miesto do Dopravy

= 1.6.18 =
* Pridaná možnosť nastaviť ID bankového účtu

= 1.6.17 =
* Pridaný tag [NON_VARIATIONS_ATTRIBUTES] do popisu produktu

= 1.6.16 =
* Doplnené preklady pre češtinu

= 1.6.15 =
* Presunuté číslo objednávky z poznámky do údajov faktúry

= 1.6.14 =
* Pridaná možnosť filtrovania typu vytvorenej/upravenej faktúry

= 1.6.13 =
* Zmena zobrazovania ceny a zľavy pre produkty so zľavou

= 1.6.12 =
* Zmenený výpočet ceny položky bez DPH

= 1.6.11 =
* Opravené delenie nulou pri nulovej dani

= 1.6.10 =
* Opravené delenie nulou pri produktoch zadarmo

= 1.6.9 =
* Opravené prekladanie nadpisov v objednávke a emailoch

= 1.6.8 =
* Opravená chyba v názve variabilných produktov

= 1.6.7 =
* Nastavenie jazyka faktúry už pri jej vytvorení
* Zmena zobrazovania ceny a zľavy pre produkty so zľavou

= 1.6.6 =
* Pridaná možnosť nastaviť názov položky pre poštovné

= 1.6.5 =
* Opravená chyba prejavujúca sa v PHP 7.0+

= 1.6.4 =
* Pridaná možnosť nastaviť Logo ID

= 1.6.3 =
* Nová verzia SuperFaktúra API klienta
* Pridaná možnosť nastaviť Company ID
* Pridané spôsoby platby zo SuperFaktúry
* Pridané nastavenia pokladní
* Obnovenie podpory free pluginu

= 1.6.2 =
* Opravena kompatibilita s pluginom WooCommerce Wholesale Pricing

= 1.6.1 =
* Opravené označovanie faktúry ako poslanej e-mailom
* Ukončenie aktívneho vývoja a podpory free pluginu

= 1.6.0 =
* Mapovanie zadaného spôsobu prispôsobené novým zónam dopravy

= 1.5.12 =
* Pridaná možnosť filtrovať vo faktúrach výber SuperFaktúra číselníka

= 1.5.11 =
* Pridané nastavenie zobrazovania čísla objednávky vo faktúre
* Pridaná možnosť nastavenia jazyka faktúry

= 1.5.10 =
* Pridaný odkaz na proformu a faktúru do zoznamu objednávok, ktorý zákazník vidí na stránke „Môj účet“

= 1.5.9 =
* Pridaná podpora pre Sequential Order Numbers Pro

= 1.5.8 =
* Opravená kompatibilita s WordPress 4.6

= 1.5.7 =
* Pridaná možnosť filtrovať čísla faktúr

= 1.5.6 =
* Pridaná možnosť nastaviť si ako má vyzerať popis produktu vo faktúre

= 1.5.5 =
* Opravená chyba generovania faktúr pri platbe prevodom na účet alebo v hotovosti

= 1.5.4 =
* Pri číslovaní faktúr je teraz možné použiť aj číslo objednávky (ORDER_NUMBER)
* Pridaná možnosť určiť si variabilný symbol
* Pridaná informácia pre SuperFaktúru o poslaní faktúry emailom
* Opravené formátovanie textu v emailoch

= 1.5.0 =
* Opravené aplikovanie zliav.

= 1.4.16 =
* Úprava kalkulácia dane pri poplatoch.

= 1.4.15 =
* Pridané zobrazovanie popisu variácie produktu.

= 1.4.14 =
* Pridané posielanie čísla objednávky ako variabilného symbolu.

= 1.4.13 =
* Pridaná možnosť pregenerovať nezaplatenú faktúru.

= 1.4.12 =
* Fixed item subtotal rounding.

= 1.4.11 =
* Upravené posielanie fakturačnej a dodacej adresy

= 1.4.10 =
* Opravená zľava pri produkte vo výpredaji

= 1.4.9 =
* Opravené aplikácia kupónov
* Opravené zamenené zadanie telefónom a emailom
* Pridaná možnosť zobrazovať popisky pod jednotlivými položkami faktúry

= 1.4.7 =
* Opravené aplikovanie zľav pri zadaní konkrétnej sumy
* Pridané zarátavanie poplatkov

= 1.4.6 =
* Opravené vystavovanie faktúr pri variáciách produktov

= 1.4.5 =
* Pridaná možnosť nastaviť, pri ktorých spôsoboch dodania sa na faktúre zobrazuje dátum dodania
* Opravené vytváranie faktúr pre českú verziu SuperFaktura.cz
* Opravené prehodené telefónne číslo a email klienta
* Opravené správne vypočítavanie zľavových kupónov (momentálne nie je možné miešať percentuálne zľavy a zľavy na konkrétnu sumu, SuperFaktúra vždy upredností percentá)

= 1.4.0 =
* Vo faktúre sa zobrazujú zľavnené produkty
* Opravená zľava pri aplikovaní kupónu
* Pridaná možnosť vlastných komentárov
* Štát sa teraz klientom priraďuje správne

= 1.3.0 =
* Pridaný oznam o daňovej povinnosti
* Zobrazuje sa celý názov štátu
* Predĺžená doba získavania PDF faktúry z API servera SuperFaktúry, aby neostala táto hodnota prázdna

= 1.2.3 =
* Opravené zobrazovanie štátu odberateľa na faktúre

= 1.2.2 =
* Opravený problém zmiznutých nastavení

= 1.2.1 =
* Opravené generovanie faktúr

= 1.2 =
* Kompatibilita s Woocommerce 2.2
* Pridaná možnosť vybrať si slovenskú alebo českú verziu

= 1.1.6 =
* Opravené delenie nulou pri poštovnom zadarmo

= 1.1.5 =
* Opravené prekladanie pomocou po/mo súborov
* Pridané slovenské jazykové súbory
* Automatické pridávanie čísla objednávky do poznámky

= 1.1.4 =
* Opravená kompatibilita s WooCommerce 2.1

= 1.1.3 =
* V zozname modulov pribudla moznost Settings
* Opravena chyba, ktora sa vyskytovala pri zmene stavu objednavky
* Pridane zobrazovanie postovneho na fakture
* Pridane cislo objednavky vo fakture
* Zmeneny vypocet dane

= 1.1.2 =
* Opravené nezobrazovanie názvu firmy vo faktúre

= 1.1.1 =
* Opravený bug v dani.
* Pridané posielane faktúry zákazníkovi mailom (odkaz na stiahnutie faktúry)

= 1.1.0 =
* Pridaný link na faktúru do emailu.

= 1.0.0 =
Prvotné vydanie.
