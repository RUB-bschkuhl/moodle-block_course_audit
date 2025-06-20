<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * German language pack for Course audit
 *
 * @package    block_course_audit
 * @category   string
 * @copyright  2025 Bastian Schmidt-Kuhl <bastian.schmidt-kuhl@ruhr-uni-bochum.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// Core block strings
$string['pluginname'] = 'Kurs-Prüfer';
$string['addinstance'] = 'Neuen Kursanalyse-Block hinzufügen';
$string['myaddinstance'] = 'Neuen Kursanalyse-Block zur \'Mein Moodle\'\'-Seite hinzufügen';

// Section related strings
$string['section'] = 'Abschnitt';
$string['section_title'] = 'Abschnittsbewertung';
$string['summary_title'] = 'Zusammenfassung';
$string['disclaimer_title'] = 'Kurs-Prüfer Informationen & Richtlinien';
$string['disclaimer_button'] = 'Prüfer starten';
$string['start_hint'] = 'Kurs-Prüfer starten';

// Navigation and UI elements
$string['page'] = 'Seite';
$string['previous'] = 'Zurück';
$string['next'] = 'Weiter';

// Module related strings
$string['modules'] = 'Module';
$string['nomodules'] = 'Keine Aktivitäten in diesem Abschnitt';
$string['norestrictions'] = 'Keine Einschränkungen in diesem Abschnitt';

// Disclaimer and documentation related
$string['documentation_link'] = 'Dokumentations-documentation öffnen';
$string['documentation_title'] = 'Kurs-Prüfer Dokumentation (TODO)';
$string['documentation_heading'] = 'Richtlinien & Best Practices (TODO)';

// Rules
$string['rule_category_hint'] = 'Hinweis';
$string['rule_category_action'] = 'Aktion';

// Rules - PDF Only
$string['rule_pdf_only_name'] = 'Ausschließlich PDF-Ressourcen';
$string['rule_pdf_only_description'] = 'Prüft, ob ein Abschnitt nur PDF-Ressourcen enthält';
$string['rule_pdf_only_empty_section'] = 'Der Abschnitt ist leer. Bitte fügen Sie Ressourcen hinzu.';
$string['rule_pdf_only_non_pdf_resources'] = 'Der Abschnitt enthält Nicht-PDF-Ressourcen:';
$string['rule_pdf_only_non_pdf_resource_item'] = '- "{$a->name}" ({$a->type})';
$string['rule_pdf_only_success'] = 'Alle {$a->count} Ressourcen im Abschnitt sind PDFs.';

// Standardisierte Schlüsselnamen mit section_ Präfix für PDFs
$string['rule_section_has_pdfs_name'] = 'Vorhandensein von PDF-Ressourcen im Abschnitt';
$string['rule_section_has_pdfs_description'] = 'Prüft, ob ein Abschnitt nur PDF-Ressourcen enthält';
$string['rule_section_has_pdfs_empty_section'] = 'Der Abschnitt ist leer. Bitte fügen Sie Ressourcen hinzu.';
$string['rule_section_has_pdfs_non_pdf_resources'] = 'Der Abschnitt enthält Nicht-PDF-Ressourcen:';
$string['rule_section_has_pdfs_non_pdf_resource_item'] = '- "{$a->name}" ({$a->type})';
$string['rule_section_has_pdfs_success'] = 'Der Abschnitt enthält {$a->count} PDF-Ressource(n).';

// Rules - Has Connections
$string['rule_has_connections_name'] = 'Aktivitätsverknüpfungen';
$string['rule_has_connections_description'] = 'Prüft, ob Aktivitäten in einem Abschnitt durch Abschlussbedingungen verbunden sind';
$string['rule_has_connections_empty_section'] = 'Der Abschnitt ist leer. Bitte fügen Sie Aktivitäten hinzu.';
$string['rule_has_connections_single_module'] = 'Der Abschnitt enthält nur eine Aktivität ("{$a->name}"). Mindestens zwei Aktivitäten sind erforderlich, um Verbindungen zu erstellen.';
$string['rule_has_connections_no_conditions'] = 'Keine Aktivitäten in diesem Abschnitt haben Abschlussbedingungen eingerichtet. Bitte fügen Sie Bedingungen hinzu, um einen Lernpfad zu erstellen.';
$string['rule_has_connections_success'] = '{$a->count} Aktivitäten haben Abschlussbedingungen eingerichtet.';
$string['rule_has_connections_module_with_condition'] = '- "{$a->name}" hat Abschlussbedingungen';
$string['rule_has_connections_some_without_conditions'] = '{$a->count} Aktivitäten haben keine Abschlussbedingungen:';
$string['rule_has_connections_module_without_condition'] = '- "{$a->name}" hat keine Abschlussbedingungen';

// Standardisierte Schlüsselnamen mit section_ Präfix
$string['rule_section_has_connections_name'] = 'Aktivitätsverknüpfungen im Abschnitt';
$string['rule_section_has_connections_description'] = 'Prüft, ob Aktivitäten in einem Abschnitt durch Abschlussbedingungen verbunden sind';
$string['rule_section_has_connections_empty_section'] = 'Der Abschnitt ist leer. Bitte fügen Sie Aktivitäten hinzu.';
$string['rule_section_has_connections_single_module'] = 'Der Abschnitt enthält nur eine Aktivität ("{$a->name}"). Mindestens zwei Aktivitäten sind erforderlich, um Verbindungen zu erstellen.';
$string['rule_section_has_connections_no_conditions'] = 'Keine Aktivitäten in diesem Abschnitt haben Abschlussbedingungen eingerichtet. Bitte fügen Sie Bedingungen hinzu, um einen Lernpfad zu erstellen.';
$string['rule_section_has_connections_success'] = '{$a->count} Aktivitäten haben Abschlussbedingungen eingerichtet.';
$string['rule_section_has_connections_module_with_condition'] = '- "{$a->name}" hat Abschlussbedingungen';
$string['rule_section_has_connections_some_without_conditions'] = '{$a->count} Aktivitäten haben keine Abschlussbedingungen:';
$string['rule_section_has_connections_module_without_condition'] = '- "{$a->name}" hat keine Abschlussbedingungen';

// Rules - Has Label - used in rule implementations
$string['rule_has_label_name'] = 'Vorhandensein von Textfeldern';
$string['rule_has_label_description'] = 'Prüft, ob ein Abschnitt ein Textfeld enthält';
$string['rule_has_label_empty_section'] = 'Der Abschnitt ist leer. Bitte fügen Sie Ressourcen hinzu.';
$string['rule_has_label_success'] = 'Der Abschnitt enthält ein Textfeld.';
$string['rule_has_label_failure'] = 'Der Abschnitt enthält kein Textfeld (Label). Textfelder helfen, Inhalte zu strukturieren und Lernenden klare Anweisungen zu geben. Erwägen Sie, ein Textfeld hinzuzufügen, um:<ul><li>Klare Überschriften für Inhaltsblöcke bereitzustellen.</li><li>Kurze Anleitungen oder Kontext für Aktivitäten zu bieten.</li><li>Lange Listen von Materialien oder Aktivitäten visuell aufzulockern.</li></ul>';
$string['button_add_label'] = 'Textfeld hinzufügen';
$string['label_added_success'] = 'Textfeld erfolgreich hinzugefügt';
$string['label_added_failure'] = 'Textfeld konnte nicht hinzugefügt werden';
$string['label_intro'] = 'Nutzen Sie Textfelder, um erklärende Texte, Anweisungen oder Überschriften direkt in einem Kursabschnitt hinzuzufügen. Dies hilft dabei, Inhalte zu strukturieren und Lernende zu leiten.';
$string['label_name'] = 'Neues Textfeld';

// Summary related
$string['summary_heading'] = 'Zusammenfassung der Kursüberprüfung';
$string['summary_button'] = 'Prüfen beenden';

// Error messages
$string['error_invalid_module'] = 'Angegebene Aktivität konnte nicht gefunden werden';
$string['error_permission_denied'] = 'Sie haben keine Berechtigung, diesen Kurs zu ändern';
$string['analysisfailed'] = 'Analyse fehlgeschlagen: {$a}';

// Tour creation strings
$string['creatingtour'] = 'Tour wird erstellt...';
$string['toursuccess'] = 'Tour erfolgreich erstellt!';
$string['startaudit'] = 'Prüfer-Tour starten';
$string['startaudit_help'] = 'Startet eine interaktive Tour durch die Kurs-Prüfer-Funktionen';
$string['tourstart_button'] = 'Tour starten';
$string['tourfinished'] = 'Tour beendet';
$string['tour_introduction'] = 'Willkommen zur Kurs-Prüfer-Tour! Diese geführte Erfahrung hilft Ihnen, Ihren Kurs zu verbessern durch:<ul><li>Analyse jedes Abschnitts auf Inhaltsvielfalt und Teilnehmerengagement</li><li>Identifizierung fehlender Verbindungen zwischen Aktivitäten, die den Lernfluss stören könnten</li><li>Vorschläge zur Verbesserung des Lernerlebnisses</li><li>Bereitstellung umsetzbarer Rückmeldungen zu Aktivitätstypen, Lernpfaden und Ressourcenorganisation</li></ul>Verwenden Sie die Navigationsschaltflächen, um durch jeden Abschnitt zu navigieren. Die Tour hebt Bereiche hervor, die Aufmerksamkeit benötigen, mit spezifischen Empfehlungen. Am Ende der Tour erhalten Sie eine umfassende Zusammenfassung mit einer Checkliste aller Prüfer-Ergebnisse, um Ihnen zu helfen, Ihren Fortschritt bei der Optimierung Ihrer Kursstruktur zu verfolgen.';

// API and results strings
$string['noauditresults'] = 'Keine Prüf-Ergebnisse für diesen Kurs gefunden.';
$string['noauditresultsfound'] = 'Keine Prüf-Ergebnisse für Tour-ID {$a} gefunden.';
$string['auditresultsfetched'] = 'Prüf-Ergebnisse erfolgreich abgerufen.';
$string['loadingsummary'] = 'Lade Prüf-Zusammenfassung...';
$string['summaryerror'] = 'Fehler beim Laden der Zusammenfassung';

// Capability strings
$string['course_audit:addinstance'] = 'Neuen Kurs-Prüfer-Block hinzufügen';
$string['course_audit:myaddinstance'] = 'Neuen Kurs-Prüfer-Block zur \'Mein Moodle\'\'-Seite hinzufügen';
$string['course_audit:view'] = 'Kurs-Prüfer-Informationen anzeigen';

// Scheduled task string
$string['cleanup_audit_tours'] = 'Aufräumen der Audit-Touren Aufgabe';

$string['showdetails'] = 'Details anzeigen';
$string['close'] = 'Schließen';

// Hinzufügen der fehlenden Strings
$string['status_done'] = 'Abgeschlossen';
$string['status_todo'] = 'Ausstehend';

// Rule: section_has_quiz
$string['rule_section_has_quiz_name'] = 'Vorhandensein von Tests im Abschnitt';
$string['rule_section_has_quiz_description'] = 'Prüft, ob der Abschnitt mindestens ein Quiz enthält.';
$string['rule_section_has_quiz_success'] = 'Abschnitt enthält ein Quiz: {$a->quizname}';
$string['rule_section_has_quiz_failure'] = 'Der Abschnitt enthält keine Quiz-Aktivitäten. Quiz-Aktivitäten sind wertvoll, um das Verständnis zu überprüfen und das Gelernte zu festigen. Erwägen Sie, ein Quiz hinzuzufügen, um:<ul><li>Das Verständnis der Lernenden für die Inhalte des Abschnitts zu testen.</li><li>Sofortiges Feedback zu geben und Wissenslücken aufzuzeigen.</li><li>Aktives Erinnern und die Auseinandersetzung mit dem Material zu fördern.</li></ul>';
$string['rule_section_has_quiz_empty_section'] = 'Abschnitt ist leer, kann kein Quiz enthalten.';

// Rule: course_has_section
$string['rule_course_has_section_name'] = 'Vorhandensein von Kursabschnitten';

// Settings page strings
$string['settings_heading'] = 'Kurs-Prüfer Einstellungen';
$string['example_setting_name'] = 'Beispiel Texteinstellung';
$string['example_setting_desc'] = 'Dies ist eine Beispiel-Texteinstellung für den Kurs-Prüfer-Block.';
$string['settings_link_description'] = 'Um die Einstellungen für den Kurs-Prüfer-Block zu konfigurieren, gehen Sie bitte zu <a href="{$a}">Block-Einstellungen</a>.';

// Standardisierte Schlüsselnamen mit section_ Präfix für Labels
$string['rule_section_has_label_name'] = 'Vorhandensein von Textfeldern im Abschnitt';
$string['rule_section_has_label_description'] = 'Prüft, ob ein Abschnitt ein Textfeld enthält';
$string['rule_section_has_label_empty_section'] = 'Der Abschnitt ist leer. Bitte fügen Sie Ressourcen hinzu.';
$string['rule_section_has_label_success'] = 'Der Abschnitt enthält ein Textfeld.';
$string['rule_section_has_label_failure'] = 'Der Abschnitt enthält kein Textfeld (Label). Textfelder helfen, Inhalte zu strukturieren und Lernenden klare Anweisungen zu geben. Erwägen Sie, ein Textfeld hinzuzufügen, um:<ul><li>Klare Überschriften für Inhaltsblöcke bereitzustellen.</li><li>Kurze Anleitungen oder Kontext für Aktivitäten zu bieten.</li><li>Lange Listen von Materialien oder Aktivitäten visuell aufzulockern.</li></ul>';

// Strings for enable_repeatable external function
$string['quiznotfound'] = 'Quiz mit ID {$a->id} nicht gefunden.';
$string['repeatalreadyenabled'] = 'Quiz ist bereits auf unbegrenzte Versuche eingestellt.';
$string['errorupdatequiz'] = 'Fehler beim Aktualisieren der Quiz-Einstellungen.';
$string['repeatenabledsuccess'] = 'Quiz-Versuche erfolgreich auf unbegrenzt gesetzt.';
$string['rule_quiz_is_repeatable_failure'] = 'Quiz erlaubt {$a->attempts} Versuch(e). Für Übungszwecke oder formative Bewertungen können unbegrenzte Versuche vorteilhaft sein. Erwägen Sie, unbegrenzte Versuche zu aktivieren, wenn:<ul><li>Das Quiz der Selbsteinschätzung und dem Lernen dient, nicht der formalen Benotung.</li><li>Sie Studierenden ermöglichen möchten, so lange zu üben, bis sie den Stoff beherrschen.</li><li>Das Ziel ist, eine wiederholte Auseinandersetzung mit den Quizinhalten zu fördern.</li></ul>';

// Rule: quiz_is_repeatable
$string['rule_quiz_is_repeatable_name'] = 'Wiederholbarkeit von Tests';
$string['rule_quiz_is_repeatable_description'] = 'Prüft, ob ein Quiz auf unbegrenzte Versuche eingestellt ist.';
$string['button_enable_repeatable'] = 'Unbegrenzte Versuche aktivieren';
$string['startnewaudit'] = 'Neue Überprüfung starten';
$string['lastaudit'] = 'Letzte Überprüfung:';
$string['checksprocessed'] = 'Gesamt';
$string['passedrules'] = 'Bestanden';
$string['failedrules'] = 'Fehlgeschlagen';

$string['courselevel'] = 'Kurs Ebene';
$string['startnewaudit'] = 'Neue Überprüfung starten';

$string['settings:managesettings'] = 'Kurs-Prüfer Einstellungen';
$string['settings:placeholder'] = 'Platzhalter';
$string['settings:placeholder_desc'] = 'Platzhalter für den Kurs-Prüfer-Block';

// Rule management
$string['course_audit:managerules'] = 'Regeln verwalten';
$string['managerules'] = 'Regeln verwalten';
$string['editrule'] = 'Regel bearbeiten';

// Comparison operators for rules
$string['equals'] = 'ist gleich';
$string['contains'] = 'enthält';
$string['regexmatches'] = 'entspricht Regex';
$string['greaterthan'] = 'ist größer als';
$string['lessthan'] = 'ist kleiner als';
$string['greaterthanorequal'] = 'ist größer oder gleich';
$string['lessthanorequal'] = 'ist kleiner oder gleich';
$string['isempty'] = 'ist leer';
$string['isnotempty'] = 'ist nicht leer';
$string['any'] = 'beliebig';

// Content comparison
$string['contentcompareoperator'] = 'Anzahl-Vergleich';
$string['contentcount'] = 'Zahl';
$string['source'] = 'Geprüftes Element';
$string['othersource'] = 'Andere Quelle';
$string['othertarget'] = 'Anderes Ziel';
$string['firstinstance'] = 'Erste';
$string['lastinstance'] = 'Letzte';
$string['target_setting'] = 'Einstellung';
$string['target_content'] = 'Inhalt-Typ';
$string['createrule'] = 'Regel erstellen';
$string['editingrule'] = 'Regel bearbeiten: {$a}';
$string['creatingrule'] = 'Neue Regel erstellen';

// Rule form strings
$string['ruledetails'] = 'Regeldetails';
$string['rulename'] = 'Regelname';
$string['ruledescription'] = 'Regelbeschreibung';
$string['preconditions'] = 'Vorbedingungen';
$string['preconditions_help'] = 'Wählen Sie Regeln aus, die erfolgreich sein müssen, bevor diese Regel geprüft wird. Halten Sie Strg/Cmd gedrückt, um mehrere Regeln auszuwählen.';
$string['existinggroup'] = 'Zu bestehender Gruppe hinzufügen';
$string['newgroupname'] = 'Oder neue Gruppe erstellen';
$string['newgroupname_help'] = 'Geben Sie einen Namen ein, um eine neue Gruppe für diese Regel zu erstellen. Lassen Sie das Feld leer, wenn Sie die Regel zu einer bestehenden Gruppe hinzufügen möchten.';
$string['selectgroup'] = 'Gruppe auswählen...';
$string['logicaloperator'] = 'Verknüpfen mit';
$string['not'] = 'Diese Prüfung umkehren (NICHT)';
$string['conditiontype'] = 'Typ';
$string['has_setting'] = 'Einstellungswert';
$string['has_content'] = 'Inhaltsanzahl';
$string['compareoperator'] = 'Vergleich';
$string['valuetocompare'] = 'Erwarteter Wert';
$string['valuetype'] = 'Wertetyp';

// Resolution strings
$string['resolutioncontext'] = 'Lösungskontext';
$string['resolutiontype'] = 'Lösungstyp';
$string['hint'] = 'Hinweis';
$string['action'] = 'Aktion';
$string['show'] = 'Anzeigen';
$string['hintmessage'] = 'Hinweisnachricht';
$string['showmessage'] = 'Anzeigemessage';
$string['actiontype'] = 'Aktionstyp';
$string['changesetting'] = 'Einstellung ändern';
$string['addcontent'] = 'Inhalt hinzufügen';
$string['addcontenttype'] = 'Inhaltstyp hinzufügen';
$string['settingorcontent'] = 'Einstellung oder Inhalt';
$string['newsettingvalue'] = 'Neuer Einstellungswert';
$string['choose'] = 'Auswählen...';
$string['addsection'] = 'Abschnitt hinzufügen';
$string['addassignment'] = 'Aufgabe hinzufügen';
$string['addquiz'] = 'Test hinzufügen';
$string['addforum'] = 'Forum hinzufügen';
$string['addwiki'] = 'Wiki hinzufügen';
$string['addfolder'] = 'Ordner hinzufügen';
$string['addurl'] = 'URL hinzufügen';
$string['addpage'] = 'Seite hinzufügen';
$string['addbook'] = 'Buch hinzufügen';
$string['addmultichoice'] = 'Multiple-Choice-Frage hinzufügen';
$string['addtruefalse'] = 'Wahr/Falsch-Frage hinzufügen';
$string['addessay'] = 'Textfrage hinzufügen';
$string['addshortanswer'] = 'Kurzantwort-Frage hinzufügen';
$string['addnumerical'] = 'Numerische Frage hinzufügen';
$string['addmatching'] = 'Zuordnungsfrage hinzufügen';
$string['addcloze'] = 'Lückentext-Frage hinzufügen';

// Form validation and saving
$string['rulesaved'] = 'Regel erfolgreich gespeichert';
$string['rules'] = 'Regeln';

// Rule form - Basic fields
$string['rule_name'] = 'Regelname';
$string['rule_name_help'] = 'Ein eindeutiger Name für diese Regel';
$string['rule_description'] = 'Beschreibung';
$string['rule_description_help'] = 'Detaillierte Beschreibung dessen, was diese Regel überprüft';
$string['rule_active'] = 'Aktiv';
$string['rule_active_help'] = 'Ob diese Regel derzeit aktiv ist und ausgeführt wird';

// Rule form - Checks section
$string['checks'] = 'Prüfungen';
$string['checks_help'] = 'Bedingungen, die erfüllt sein müssen, damit diese Regel ausgelöst wird';
$string['check_type'] = 'Prüfungstyp';
$string['check_type_help'] = 'Art der durchzuführenden Prüfung';
$string['check_type_setting'] = 'Einstellung';
$string['check_type_content'] = 'Inhalt';

// Source field
$string['source'] = 'Quelle';
$string['source_help'] = 'Das Moodle-Element, das überprüft werden soll';
$string['source_course'] = 'Kurs';
$string['source_section'] = 'Abschnitt';
$string['source_assign'] = 'Aufgabe';
$string['source_quiz'] = 'Test';
$string['source_forum'] = 'Forum';
$string['source_resource'] = 'Ressource';
$string['source_url'] = 'URL';
$string['source_page'] = 'Seite';
$string['source_book'] = 'Buch';
$string['source_folder'] = 'Ordner';
$string['source_workshop'] = 'Workshop';
$string['source_wiki'] = 'Wiki';
$string['source_glossary'] = 'Glossar';
$string['source_lesson'] = 'Lektion';
$string['source_scorm'] = 'SCORM-Paket';

// Target fields - Settings
$string['target_setting'] = 'Zieleinstellung';
$string['target_setting_help'] = 'Die spezifische Einstellung, die überprüft werden soll';
$string['target_content'] = 'Zielinhalt';
$string['target_content_help'] = 'Der spezifische Inhalt, der überprüft werden soll';

// Course settings targets
$string['target_course_fullname'] = 'Vollständiger Name';
$string['target_course_shortname'] = 'Kurzer Name';
$string['target_course_visible'] = 'Sichtbar';
$string['target_course_startdate'] = 'Startdatum';
$string['target_course_enddate'] = 'Enddatum';
$string['target_course_format'] = 'Kursformat';
$string['target_course_numsections'] = 'Anzahl Abschnitte';
$string['target_course_groupmode'] = 'Gruppenmodus';
$string['target_course_enablecompletion'] = 'Abschlussverfolgung aktiviert';

// Section settings targets
$string['target_section_name'] = 'Name';
$string['target_section_summary'] = 'Zusammenfassung';
$string['target_section_visible'] = 'Sichtbar';

// Quiz settings targets
$string['target_quiz_timeopen'] = 'Öffnungszeit';
$string['target_quiz_timeclose'] = 'Schließungszeit';
$string['target_quiz_attempts'] = 'Erlaubte Versuche';
$string['target_quiz_timelimit'] = 'Zeitbegrenzung';
$string['target_quiz_grade'] = 'Bewertung';

// Assignment settings targets
$string['target_assign_duedate'] = 'Abgabetermin';
$string['target_assign_cutoffdate'] = 'Stichtag';
$string['target_assign_allowsubmissionsfromdate'] = 'Abgaben erlaubt ab';
$string['target_assign_grade'] = 'Bewertung';

// Course content targets
$string['target_course_has_sections'] = 'Hat Abschnitte';
$string['target_course_has_activities'] = 'Hat Aktivitäten';

// Section content targets
$string['target_section_has_activities'] = 'Hat Aktivitäten';
$string['target_section_has_resources'] = 'Hat Ressourcen';

// Quiz content targets
$string['target_quiz_has_questions'] = 'Hat Fragen';
$string['target_quiz_has_multichoice'] = 'Hat Multiple-Choice-Fragen';
$string['target_quiz_has_truefalse'] = 'Hat Wahr/Falsch-Fragen';
$string['target_quiz_has_essay'] = 'Hat Textfragen';

// Assignment content targets
$string['target_assign_has_submissions'] = 'Hat Abgaben';
$string['target_assign_has_rubric'] = 'Hat Bewertungsraster';

// Comparison operators
$string['comp'] = 'Vergleich';
$string['comp_help'] = 'Vergleichsoperator für die Prüfung';
$string['comp_eq'] = 'gleich';
$string['comp_neq'] = 'ungleich';
$string['comp_gt'] = 'größer als';
$string['comp_gte'] = 'größer oder gleich';
$string['comp_lt'] = 'kleiner als';
$string['comp_lte'] = 'kleiner oder gleich';
$string['comp_contains'] = 'enthält';
$string['comp_not_contains'] = 'enthält nicht';
$string['comp_empty'] = 'ist leer';
$string['comp_not_empty'] = 'ist nicht leer';

// Value fields
$string['value'] = 'Wert';
$string['value_help'] = 'Der Wert zum Vergleichen';
$string['value_type'] = 'Wertetyp';
$string['value_type_help'] = 'Der Datentyp des Vergleichswerts';
$string['value_type_string'] = 'Text';
$string['value_type_int'] = 'Ganzzahl';
$string['value_type_float'] = 'Dezimalzahl';
$string['value_type_bool'] = 'Boolean';
$string['value_type_date'] = 'Datum';

// Logical operators
$string['logic_operator'] = 'Logischer Operator';
$string['logic_operator_help'] = 'Logischer Operator zur Verknüpfung mit der nächsten Prüfung';
$string['logic_and'] = 'UND';
$string['logic_or'] = 'ODER';

// Resolutions section
$string['resolutions'] = 'Lösungen';
$string['resolutions_help'] = 'Aktionen oder Hinweise, die angezeigt werden, wenn diese Regel ausgelöst wird';
$string['resolution_type'] = 'Lösungstyp';
$string['resolution_type_help'] = 'Art der Lösung';
$string['resolution_type_hint'] = 'Hinweis';
$string['resolution_type_action'] = 'Aktion';
$string['resolution_message'] = 'Nachricht';
$string['resolution_message_help'] = 'Die Nachricht, die dem Benutzer angezeigt wird';

// Form buttons and validation
$string['addmoreitems'] = 'Weitere {no} Elemente hinzufügen';
$string['required'] = 'Dieses Feld ist erforderlich';

// Success/error messages
$string['rulecreated'] = 'Regel erfolgreich erstellt';
$string['ruleupdated'] = 'Regel erfolgreich aktualisiert';
$string['ruledeleted'] = 'Regel erfolgreich gelöscht';
$string['error_creating_rule'] = 'Fehler beim Erstellen der Regel';
$string['error_updating_rule'] = 'Fehler beim Aktualisieren der Regel';
$string['error_deleting_rule'] = 'Fehler beim Löschen der Regel';