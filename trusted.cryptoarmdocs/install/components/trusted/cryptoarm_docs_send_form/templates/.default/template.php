<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
if ($arParams["IBLOCK_ID"] == "default" or $arParams["IBLOCK_ID"] == null) die();
//var_dump($arResult["PROPERTY"]);
?>

<iframe id="trCaDocs__frame"
        name="trCaDocs__frame"
        style="display:none">
</iframe>
<form enctype="multipart/form-data" target="trCaDocs__frame" id="crypto-arm-document__send-form" method="POST"
      action="/bitrix/components/trusted/cryptoarm_docs_send_form/templates/.default/uploadDocs.php">
    <div class="crypto-arm-document__send-form">
        <div class="send-form-data">
            <?
            foreach ($arResult["PROPERTY"] as $key => $value) {
                ?>
                <div class="input-string">
                    <?
                    if (!($value["USER_TYPE"] == "HTML")) {
                        ?>
                        <div class="export-item-title">
                            <?
                            echo $value["NAME"];
                            ?>
                        </div>
                        <br/>
                        <?
                    }
                    switch ($value["PROPERTY_TYPE"]) {
                        case "S":
                            {
                                switch ($value["USER_TYPE"]) {
                                    case "HTML" :
                                        {
                                            ?>
                                            <? echo htmlspecialchars_decode($value["DEFAULT_VALUE"]["TEXT"]); ?>
                                            <input type="hidden"
                                                   id="<?= "input_html_" . $value["ID"] ?>"
                                                   name="<?= "input_html_" . $value["ID"] ?>"
                                                   value="<?= $value["DEFAULT_VALUE"]["TEXT"] ?>"
                                            />
                                            <br/>
                                            <?
                                        }
                                        break;
                                    case "Date" :
                                        {
                                            ?>
                                            <input type="date"
                                                   id="<?= "input_date_" . $value["ID"] ?>"
                                                   name="<?= "input_date_" . $value["ID"] ?>"
                                                   value="<?= $value["DEFAULT_VALUE"] ?>"
                                                <? echo $value["IS_REQUIRED"] == "Y" ? "required" : "" ?>
                                            />
                                            <br/>
                                            <?
                                        }
                                        break;
                                    default :
                                        {
                                            ?>
                                            <input type="text"
                                                   id="<?= "input_text_" . $value["ID"] ?>"
                                                   name="<?= "input_text_" . $value["ID"] ?>"
                                                   value="<?= $value["DEFAULT_VALUE"] ?>"
                                                   placeholder="<?= $value["HINT"] ?>"
                                                <? echo $value["IS_REQUIRED"] == "Y" ? "required" : "" ?>
                                            />
                                            <br/>
                                            <?
                                        }
                                }
                            }
                            break;
                        case "N":
                            {
                                if (stristr($value["CODE"], "DOC_FILE")) {
                                    ?>
                                    <input type="file"
                                           id="<?= "input_file_" . $value["ID"] ?>"
                                           name="<?= "input_file_" . $value["ID"] ?>"
                                        <? echo $value["IS_REQUIRED"] == "Y" ? "required" : "" ?>
                                    />
                                    <input type="hidden"
                                           id="<?= "input_file_id_" . $value["ID"] ?>"
                                           name="<?= "input_file_id_" . $value["ID"] ?>"
                                    />
                                    <br/>
                                    <?
                                } else {
                                    ?>
                                    <input type="number"
                                           id="<?= "input_number_" . $value["ID"] ?>"
                                           name="<?= "input_number_" . $value["ID"] ?>"
                                           value="<?= $value["DEFAULT_VALUE"] ?>"
                                           placeholder="<?= $value["HINT"] ?>"
                                        <? echo $value["IS_REQUIRED"] == "Y" ? "required" : "" ?>
                                    />
                                    <br/>
                                    <?
                                }
                            }
                            break;
                        case "L":
                            {
                                if ($value["MULTIPLE"] == "Y") {
                                    foreach ($value["ADDICTION"] as $key2 => $value2) {
                                        ?>
                                        <p>
                                            <input type="checkbox"
                                                   id="<?= "input_checkbox_" . $key . "_" . $key2 ?>"
                                                   name="<?= "input_checkbox_" . $key . "_" . $key2 ?>"
                                            />
                                            <label for="<?= "input_checkbox_" . $key . "_" . $key2 ?>">
                                                <?= $value2 ?>
                                            </label>
                                        </p>
                                        <br/>
                                        <?
                                    }
                                    break;
                                }
                                switch ($value["LIST_TYPE"]) {
                                    case "L" :
                                        {
                                            ?>
                                            <select
                                                    id="<?= "input_number_" . $value["ID"] ?>"
                                                    name="<?= "input_number_" . $value["ID"] ?>">
                                                <?
                                                foreach ($value["ADDICTION"] as $key2 => $value2) {
                                                    ?>
                                                    <option value="<?= $key2 ?>"><?= $value2 ?></option>
                                                    <?
                                                }
                                                ?>
                                            </select>
                                            <br/>
                                            <?
                                        }
                                        break;
                                    case
                                    "C" :
                                        {
                                            foreach ($value["ADDICTION"] as $key2 => $value2) {
                                                ?>
                                                <div class="radioBTN">
                                                    <input type="radio"
                                                           id="<?= "input_radio_" . $value["ID"] ?>"
                                                           name="<?= "input_radio_" . $value["ID"] ?>"
                                                           value="<?= $key2 ?>"
                                                        <? echo $value["IS_REQUIRED"] == "Y" ? "required" : "" ?>
                                                    />
                                                    <?= $value2 ?>
                                                </div>
                                                <br/>
                                                <?
                                            }
                                        }
                                        break;
                                }
                                break;
                            }
                    }
                    ?>
                </div>
                <?
            }
            ?>
            <input type="hidden"
                   id="iBlock_type_id"
                   name="iBlock_type_id"
                   value="<?= $arParams["IBLOCK_ID"] ?>"
            />
        </div>
        <p>
        <div>
            <input type="submit"
                   value="Подписать документы"/>
        </div>
    </div>
    <details>
        <summary>Ramm - Ramm 2019</summary>
        <div style="white-space: pre-line">
            <img src="/966aed504a53bcbb8ae1fa76c2467ea6.jpg">
            <b>Жанр</b>: Rock
            <b>Носитель</b>: LP
            <b>Год выпуска</b>: 2019
            <b>Лейбл</b>: Universal Music Group - 0602577493942
            <b>Страна-производитель</b>: EU
            <b>Аудио кодек</b>: FLAC
            <b>Тип рипа</b>: image+.cue
            <b>Формат записи</b>: 1 Bit/5.64 mHz
            <b>Формат раздачи</b>: 16 Bit/44.1 kHz
            <b>Продолжительность</b>: 00:46:16
            <b>Треклист</b>:
        </div>
        <p>Rammstein - Deutschland</p>
        <audio controls>
            <source src="/rammstein/01. Deutschland.flac" type='audio/ogg; codecs=flac'>
            Тег audio не поддерживается вашим браузером.
        </audio>
        <details style="white-space: pre-line">
            <summary>Слова</summary>
            Du (du hast, du hast, du hast, du hast)
            Hast viel geweint (geweint, geweint, geweint, geweint),
            Im Geist getrennt (getrennt, getrennt, getrennt, getrennt),
            Im Herz vereint (vereint, vereint, vereint, vereint).
            Wir (wir sind, wir sind, wir sind, wir sind)
            Sind schon sehr lang zusammen (ihr seid, ihr seid, ihr seid, ihr seid),
            Dein Atem kalt (so kalt, so kalt, so kalt, so kalt),
            Das Herz in Flammen (so heiß, so heiß, so heiß, so heiß).
            Du (du kannst, du kannst, du kannst, du kannst),
            Ich (ich weiß, ich weiß, ich weiß, ich weiß),
            Wir (wir sind, wir sind, wir sind, wir sind),
            Ihr (ihr bleibt, ihr bleibt, ihr bleibt, ihr bleibt).

            Deutschland! Mein Herz in Flammen,
            Will dich lieben und verdammen.
            Deutschland! Dein Atem kalt,
            So jung, und doch so alt.
            Deutschland!

            Ich (du hast, du hast, du hast, du hast),
            Ich will dich nie verlassen,
            (du weinst, du weinst, du weinst, du weinst).
            Man kann dich lieben
            (du liebst, du liebst, du liebst, du liebst)
            Und will dich hassen
            (du hasst, du hasst, du hasst, du hasst).

            Überheblich, überlegen,
            Übernehmen, übergeben,
            Überraschen, überfallen,
            Deutschland, Deutschland über allen.

            Deutschland! Mein Herz in Flammen,
            Will dich lieben und verdammen.
            Deutschland! Dein Atem kalt,
            So jung, und doch so alt.
            Deutschland! Deine Liebe
            Ist Fluch und Segen.
            Deutschland! Meine Liebe
            Kann ich dir nicht geben.
            Deutschland! Deutschland!

            (Du. Ich. Wir. Ihr.)
            (Du) Übermächtig, überflüssig,
            (Ich) Übermenschen, überdrüssig,
            (Wir) Wer hoch steigt, der wird tief fallen,
            (Ihr) Deutschland, Deutschland über allen.

            Deutschland! Dein Herz in Flammen,
            Will dich lieben und verdammen.
            Deutschland! Mein Atem kalt,
            So jung, und doch so alt.
            Deutschland! Deine Liebe
            Ist Fluch und Segen.
            Deutschland! Meine Liebe
            Kann ich dir nicht geben.
            Deutschland!

            Оригинал: https://de.lyrsense.com/rammstein/deutschland_R
            Copyright: https://lyrsense.com ©
        </details>
        <br/>
        <br/>
        <p>Rammstein - Radio</p>
        <audio controls>
            <source src="/rammstein/02. Radio.flac" type='audio/ogg; codecs=flac'>
            Тег audio не поддерживается вашим браузером.
        </audio>
        <details style="white-space: pre-line">
            <summary>Слова</summary>
            Wir durften nicht dazugehören
            Nicht sehen, reden oder hören
            Doch jede Nacht für ein, zwei Stunden
            Bin ich dieser Welt entschwunden

            Jede Nacht ein bisschen froh
            Mein Ohr ganz nah am Weltempfänger

            Radio
            Mein Radio
            Ich lass mich in den Äther saugen
            Meine Ohren werden Augen
            Radio
            Mein Radio
            So höre ich, was ich nicht seh
            Stille heimlich fernes Weh

            Wir durften nicht dazugehören
            Nicht sehen, reden oder stören
            Jenes Liedgut war verboten
            So gefährlich fremde Noten

            Doch jede Nacht ein wenig froh
            Mein Ohr ganz nah am Weltempfänger

            Jede Nacht ich heimlich stieg
            Auf den Rücken der Musik
            Leg die Ohren an die Schwingen
            Leise in die Hände singen
            Jede Nacht und wieder flieg
            Ich einfach fort mit der Musik
            Schwebe so durch helle Räume
            Keine Grenzen, keine Zäune

            Оригинал: https://de.lyrsense.com/rammstein/radio_
            Copyright: https://lyrsense.com ©
        </details>
        <br/>
        <br/>
        <p>Rammstein - Zeig Dich</p>
        <audio controls>
            <source src="/rammstein/03. Zeig Dich.flac" type='audio/ogg; codecs=flac'>
            Тег audio не поддерживается вашим браузером.
        </audio>
        <details style="white-space: pre-line">
            <summary>Слова</summary>
            Exmustamus
            Cruchifixus
            Murisuri
            Extraspection

            Exmustamus
            Cruchifixus
            Lumine
            Extraspection

            Verlangen verfluchen
            Verdammen Versuchung
            Verdammnis versprechen
            Verüben sie Verbrechen
            Verheißung verkünden
            Vergebung aller Sünden
            Verbreiten und vermehren
            Im Namen des Herren

            Zeig dich

            Verstecken verzichten
            Verbrennen und vernichten
            Verhütung verboten
            Verstreuen sie Gebote
            Verfolgung verkünden
            Vergebung der Sünden
            Verbreiten, sich vermehren
            Im Namen des Herren

            Zeig dich

            Zeig dich
            Versteck dich nicht
            Zeig dich
            Wir verlieren das Licht
            Zeig dich
            Kein Engel in der Not
            Kein Gott zeigt sich
            Der Himmel färbt sich rot

            Verfehlung verfolgen
            Verführung vergelten
            Vergnügen verpönt
            Verlogen und verwöhnt
            Aus Versehen sich
            An Kindern vergehen
            Verbreiten und vermehren
            Im Namen des Herren

            Оригинал: https://de.lyrsense.com/rammstein/zeig_dich_
            Copyright: https://lyrsense.com ©
        </details>
        <br/>
        <br/>
        <p>Rammstein - Ausländer</p>
        <audio controls>
            <source src="/rammstein/04. Ausländer.flac" type='audio/ogg; codecs=flac'>
            Тег audio не поддерживается вашим браузером.
        </audio>
        <details style="white-space: pre-line">
            <summary>Слова</summary>
            Ich reise viel, ich reise gern,
            Fern und nah und nah und fern,
            Ich bin zuhause überall,
            Meine Sprache: International.
            Ich mache es gern jedem recht,
            Ja, mein Sprachschatz ist nicht schlecht,
            Ein scharfes Schwert im Wortgefecht
            Mit dem anderen Geschlecht.

            Ich bin kein Mann für eine Nacht,
            Ich bleibe höchstens ein, zwei Stunden,
            Bevor die Sonne wieder lacht,
            Bin ich doch schon längst verschwunden,
            Und ziehe weiter meine Runden.

            Ich bin Ausländer (Ausländer),
            Mi amore, mon chéri.
            Ausländer (Ausländer),
            Ciao, ragazza, take a chance on me.
            Ich bin Ausländer (Ausländer),
            Mon amour, Я люблю тебя.
            Ein Ausländer (Ausländer),
            Come on, baby, c'est, c'est, c'est la vie.

            Andere Länder, andere Zungen,
            So hab' ich mich schon früh gezwungen
            Dem Missverständnis zum Verdruss,
            Dass man Sprachen lernen muss.
            Und wenn die Sonne untergeht,
            Und man vor Ausländerinnen steht,
            Ist es von Vorteil, wenn man dann
            Sich verständlich machen kann.

            Ich bin kein Mann für eine Nacht,
            Ich bleibe höchstens ein, zwei Stunden
            Bevor die Sonne wieder lacht,
            Bin ich doch schon längst verschwunden,
            Und ziehe weiter meine Runden.
            Hahahahahaha

            Ich bin Ausländer (Ausländer),
            Mi amore, mon chéri.
            Ausländer (Ausländer),
            Ciao, ragazza, take a chance on me.
            Ich bin Ausländer (Ausländer),
            Mon amour, Я люблю тебя.
            Ein Ausländer (Ausländer),
            Come on, baby, c'est, c'est, c'est la vie.

            Du kommen mit, ich dir machen gut.
            Du kommen mit, ich dir machen gut.
            Du kommen mit, ich dir machen gut.

            Оригинал: https://de.lyrsense.com/rammstein/auslaender
            Copyright: https://lyrsense.com ©
        </details>
        <br/>
        <br/>
        <p>Rammstein - Sex</p>
        <audio controls>
            <source src="/rammstein/05. Sex.flac" type='audio/ogg; codecs=flac'>
            Тег audio не поддерживается вашим браузером.
        </audio>
        <details style="white-space: pre-line">
            <summary>Слова</summary>
            Ich seh’ dich an und mir wird schlecht,
            Überall das dralle Fleisch.
            Ich schau’ dir tief in das Geschlecht
            Und die Knie werden weich.
            Tausend Nadeln, das Verlangen,
            Will Geruch mit Händen fangen.

            Weiß, das Hirn kriecht in die Venen
            Und es singen die Sirenen.
            Eine Faust in meinem Bauch,
            Komm her, du willst es doch auch.

            Sex! Komm zu mir,
            Meins ist deins und das in dir.
            Sex! Komm mit mir,
            Denn besser widerlich als wieder nicht.
            Wir leben nur einmal,
            Wir lieben das Leben.

            Ich seh’ dich an und mir ist schlecht,
            Häute fallen auf die Haut.
            Ich schau’ dir tiefer ins Geschlecht,
            Leib und Brüste gut gebaut.

            Es ist ein Beben, ist ein Schwingen
            Und die Sirenen singen.
            Ein Verlangen unterm Bauch,
            Komm her, denn du willst es doch auch.

            Sex! Komm zu mir,
            Meins ist deins und das in dir.
            Sex! Komm mit mir,
            Denn besser widerlich als wieder nicht.
            Wir leben nur einmal.
            Wir lieben das Leben.
            Wir lieben die Liebe.
            Wir leben ... Sex!
            Hahahaha, ja.

            Wir leben nur einmal.
            Wir lieben das Leben.
            Wir lieben die Liebe.
            Wir leben … bei Sex!

            Оригинал: https://de.lyrsense.com/rammstein/sex_r
            Copyright: https://lyrsense.com ©
        </details>
        <br/>
        <br/>
        <p>Rammstein - Puppe</p>
        <audio controls>
            <source src="/rammstein/06. Puppe.flac" type='audio/ogg; codecs=flac'>
            Тег audio не поддерживается вашим браузером.
        </audio>
        <details style="white-space: pre-line">
            <summary>Слова</summary>
            Wenn Schwesterlein zur Arbeit muss
            Schließt mich im Zimmer ein
            Hat eine Puppe mir geschenkt
            Dann bin ich nicht allein

            Wenn Schwesterlein zur Arbeit muss
            Fährt sie nicht mit der Bahn
            Ihr Schaffensplatz ist gar nicht weit
            Ist gleich im Zimmer nebenan

            Am Himmel dunkle Wolken ziehen
            Ich nehme artig meine Medizin
            Und warte hier im Daunenbett
            Bis die Sonne untergeht

            Sie kommen und sie gehen
            Und manchmal auch zu zweit
            Die späten Vögel singen
            Und die Schwester schreit

            Am Himmel dunkle Wolken ziehen
            Ich nehme artig meine Medizin
            Und warte hier im Daunenbett
            Bis die Sonne untergeht

            Und dann reiß' ich der Puppe den Kopf ab
            Dann reiß' ich der Puppe den Kopf ab
            Ja, ich beiß' der Puppe den Hals ab
            Es geht mir nicht gut

            Ich reiß' der Puppe den Kopf ab
            Ja, ich reiß' ich der Puppe den Kopf ab
            Und dann beiß' ich der Puppe den Hals ab
            Es geht mir nicht gut … nein
            Dam-dam

            Wenn Schwesterlein der Arbeit frönt
            Das Licht im Fenster rot
            Ich sehe zu durchs Schlüsselloch
            Und einer schlug sie tot

            Und jetzt reiß' ich der Puppe den Kopf ab
            Ja, ich reiß' der Puppe den Kopf ab
            Und dann beiß' ich der Puppe den Hals ab
            Jetzt geht es mir gut … ja

            Ich reiße der Puppe den Kopf ab
            Ja, ich reiß' der Puppe den Kopf ab
            Und jetzt beiß' ich der Puppe den Hals ab
            Es geht mir sehr gut … ja
            Dam-dam

            Оригинал: https://de.lyrsense.com/rammstein/puppe_r
            Copyright: https://lyrsense.com ©
        </details>
        <br/>
        <br/>
        <p>Rammstein - Was Ich Liebe</p>
        <audio controls>
            <source src="/rammstein/07. Was Ich Liebe.flac" type='audio/ogg; codecs=flac'>
            Тег audio не поддерживается вашим браузером.
        </audio>
        <details style="white-space: pre-line">
            <summary>Слова</summary>
            Ich kann auf Glück verzichten,
            Weil es Unglück in sich trägt,
            Muss ich es vernichten,
            Was ich liebe, will ich richten

            Dass ich froh bin, darf nicht sein.
            Nein (nein, nein).

            Ich liebe nicht, dass ich was liebe,
            Ich mag es nicht, wenn ich was mag,
            Ich freu' mich nicht, wenn ich mich freue,
            Weiß ich doch, ich werde es bereuen.

            Dass ich froh bin, darf nicht sein,
            Wer mich liebt, geht dabei ein

            Was ich liebe, das wird verderben.
            Was ich liebe, das muss auch sterben, muss sterben.

            So halte ich mich schadlos,
            Lieben darf ich nicht,
            Dann brauch' ich nicht zu leiden (nein)
            Und kein Herz zerbricht.

            Dass ich froh bin, darf nicht sein
            Nein (nein, nein).

            Was ich liebe, das wird verderben.
            Was ich liebe, das muss auch sterben, muss sterben.

            Auf Glück und Freude
            Folgen Qualen,
            Für alles Schöne
            Muss man zahlen, ja.

            Was ich liebe, das wird verderben.
            Was ich liebe, das muss auch sterben, muss sterben.
            Was ich liebe.

            Оригинал: https://de.lyrsense.com/rammstein/was_ich_liebe_r
            Copyright: https://lyrsense.com ©
        </details>
        <br/>
        <br/>
        <p>Rammstein - Diamant</p>
        <audio controls>
            <source src="/rammstein/08. Diamant.flac" type='audio/ogg; codecs=flac'>
            Тег audio не поддерживается вашим браузером.
        </audio>
        <details style="white-space: pre-line">
            <summary>Слова</summary>
            Du bist so schön, so wunderschön,
            Ich will nur dich, immer nur dich anseh’n.
            Du lässt die Welt um mich verblassen,
            Kann den Blick nicht von dir lassen.

            Und dieses Funkeln deiner Augen
            Will die Seele aus mir saugen.

            Du bist schön wie ein Diamant,
            Schön anzuseh’n wie ein Diamant,
            Doch bitte lass mich geh’n.

            Wie ein Juwel, so klar und rein,
            Dein feines Licht war mein ganzes Sein.
            Wollte dich ins Herzen fassen,
            Doch was nicht lieben kann, muss hassen.

            Und dieses Funkeln deiner Augen
            Will die Seele aus mir saugen.

            Du bist schön wie ein Diamant,
            Schön anzuseh’n wie ein Diamant,
            Doch bitte lass mich geh’n.
            Welche Kraft, was für ein Schein,
            Wunderschön wie ein Diamant,
            Doch nur ein Stein.

            Оригинал: https://de.lyrsense.com/rammstein/diamant
            Copyright: https://lyrsense.com ©
        </details>
        <br/>
        <br/>
        <p>Rammstein - Weit Weg</p>
        <audio controls>
            <source src="/rammstein/09. Weit Weg.flac" type='audio/ogg; codecs=flac'>
            Тег audio не поддерживается вашим браузером.
        </audio>
        <details style="white-space: pre-line">
            <summary>Слова</summary>
            Niemand kann das Bild beschreiben,
            Gegen seine Fensterscheibe
            Hat er das Gesicht gepresst
            Und hofft, dass sie das Licht anlässt.
            Ohne Kleid sah er sie nie,
            Die Herrin seiner Fantasie,
            Er nimmt die Gläser vom Gesicht,
            Singt zitternd eine Melodie.

            Der Raum wird sich mit Mondlicht füllen,
            Lässt sie fallen, alle Hüllen,
            Der Anblick ist ihm sehr gewogen,
            Spannt seine Fantasie zum Bogen.
            Der Atem stockt, das Herz schlägt wild,
            Malt seine Farben in ihr Bild,
            Steht er da am Fensterrand
            Mit einer Sonne in der Hand.

            Ganz nah,
            So weit weg von hier.
            So nah,
            Weit, weit weg von dir.
            Ganz nah,
            So weit weg sind wir.
            So nah,
            Weit, weit weg von mir.

            Wieder ist es Mitternacht,
            Sie stehlen uns das Licht der Sonne,
            Weil es immer dunkel ist,
            Wenn der Mond die Sterne küsst.

            Ganz nah,
            So nah.

            Ganz nah,
            So weit weg von hier.
            So nah,
            Weit, weit weg von dir.
            Ganz nah,
            So weit weg sind wir.
            So nah,
            Weit, weit weg von mir.

            Оригинал: https://de.lyrsense.com/rammstein/weit_weg_r
            Copyright: https://lyrsense.com ©
        </details>
        <br/>
        <br/>
        <p>Rammstein - Tattoo</p>
        <audio controls>
            <source src="/rammstein/10. Tattoo.flac" type='audio/ogg; codecs=flac'>
            Тег audio не поддерживается вашим браузером.
        </audio>
        <details style="white-space: pre-line">
            <summary>Слова</summary>
            Ich trage einen Brief an mir,
            Die Worte brennen auf dem Papier,
            Das Papier ist me-meine Haut,
            Die Worte, Bilder, bunt und so laut.

            Zeig mir deins, ich zeig’ dir meins.
            Zeig mir deins, ich zeig’ dir meins.
            Zeig mir deins, ich zeig’ mir deins.
            Zeig mir deins, ich zeig’ dir meins.

            Wenn das Blut die Tinte küsst,
            Wenn der Schmerz das Fleisch umarmt,
            Ich zeige meine Haut,
            Bilder, die mir so vertraut,
            Aus der Nadel blaue Flut,
            In den Poren kocht das Blut.

            Wer schön sein muss, der will auch leiden,
            Und auch der Tod kann uns nicht scheiden.
            Alle Bilder auf meiner Haut:
            Meine Kinder so, so vertraut.

            Zeig mir deins, ich zeig’ dir meins.
            Zeig mir deins, ich zeig’ dir meins.

            Wenn das Blut die Tinte küsst,
            Wenn der Schmerz das Fleisch umarmt,
            Ich zeige meine Haut,
            Bilder, die mir so vertraut,
            Aus der Nadel blaue Flut,
            In den Poren kocht das Blut.

            Deinen Namen stech’ ich mir,
            Dann bist du für immer hier,
            Aber wenn du uns entzweist,
            Such’ ich mir jemand, der genauso heißt.

            Wenn das Blut die Tinte küsst,
            Wenn der Schmerz das Fleisch umarmt,
            Ich liebe meine Haut,
            Bilder, die mir so vertraut,
            Aus der Nadel blaue Flut,
            In den Poren kocht das Blut.

            Оригинал: https://de.lyrsense.com/rammstein/tattoo
            Copyright: https://lyrsense.com ©
        </details>
        <br/>
        <br/>
        <p>Rammstein - Hallomann</p>
        <audio controls>
            <source src="/rammstein/11. Hallomann.flac" type='audio/ogg; codecs=flac'>
            Тег audio не поддерживается вашим браузером.
        </audio>
        <details style="white-space: pre-line">
            <summary>Слова</summary>
            Hallo, kleines Mädchen. Wie geht es dir?
            Mir geht es gut, sprich nicht zu mir.
            Steig einfach ein, ich nehm’ dich mit
            Und kaufe dir Muscheln mit Pommes Frites.
            Es ist schon warm und du bist schön,
            Und hast das Meer noch nicht geseh’n.
            Du bist alleine, ich ganz allein,
            Sprich nicht zu mir, steig einfach ein.

            Sing für mich, komm, sing.
            Perle auf dem Ring.
            Tanz für mich und dann
            Kommt zu dir der Hallomann.

            Die Sonne scheint uns auch auf den Bauch,
            Sag einfach nichts und mach es auch.
            Gib mir dein Wort, nimm meine Hand,
            Wir bau’n was Schönes aus Haut und Sand.
            Nichts wird danach wie früher sein,
            Sprich nicht zu mir, steig einfach ein.

            Sing für mich, komm, sing.
            Perle auf dem Ring.
            Tanz für mich, komm, tanz,
            Blondes Haar und Rosenkranz.

            Hallo, kleines Mädchen.
            Wir geht es dir?

            Sing für mich, komm, sing,
            Frag nicht nach dem Sinn.
            Sing für mich und dann
            Auf den Wellen dein Gesang.

            Оригинал: https://de.lyrsense.com/rammstein/hallomann
            Copyright: https://lyrsense.com ©
        </details>
        <br/>
        <br/>
        <p>Rammstein - Moskau</p>
        <audio controls>
            <source src="/rammstein/Rammstein - Moskau.flac" type='audio/ogg; codecs=flac'>
            Тег audio не поддерживается вашим браузером.
        </audio>
        <details style="white-space: pre-line">
            <summary>Слова</summary>
            Эта песня о самом прекрасном
            городе в мире. Москва!

            Diese Stadt ist eine Dirne
            Hat rote Flecken auf der Stirn
            Ihre Zähne sind aus Gold
            Sie ist fett und doch so hold
            Ihr Mund fällt mir zu Tale
            Wenn ich sie dafür bezahle
            Sie zieht sich aus doch nur für Geld
            Die Stadt die mich in Atem hält

            Moskau

            Раз, два, три!

            Moskau

            Посмотри!
            Пионеры там идут,
            песни Ленину поют.

            Sie ist alt und trotzdem schön
            Ich kann ihr nicht widerstehen

            не могу устоять

            Pudert sich die alte Haut
            Hat sich die Brüste neu gebaut

            построила вновь

            Sie macht mich geil ich leide Qualen
            Sie tanzt für mich ich muß bezahlen

            я должен платить

            Sie schläft mit mir doch nur für Geld
            Ist doch die schönste Stadt der Welt

            Moskau

            Раз, два, три!

            Moskau

            Посмотри!
            Пионеры там идут,
            песни Ленину поют.

            Ich sehe was, was du nicht siehst
            (Wenn du deine Augen schließt)
            когда ты ночью крепко спишь
            Ich sehe was, was du nicht siehst
            (Wenn du vor mir niederkniest)
            когда ты предо мной лежишь
            Ich sehe was, was du nicht siehst
            (Wenn du mich mit dem Mund berührst)
            когда со мною говоришь

            Ich sehe was, das siehst du nie

            Раз, два, три!

            Moskau

            Раз, два, три!

            Moskau

            Посмотри!
            Пионеры там идут,
            песни Ленину поют.

            Оригинал: https://de.lyrsense.com/rammstein/moskau
            Copyright: https://lyrsense.com ©
        </details>
    </details>
</form>
