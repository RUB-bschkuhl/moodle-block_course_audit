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

// Rule Form & Management
$string['managerules'] = 'Audit-Regeln verwalten';
$string['createnewrule'] = 'Neue Regel erstellen';
$string['editrule'] = 'Regel bearbeiten';
$string['ruleset'] = 'Regelsatz';
$string['selectruleset'] = 'Regelsatz auswählen';
$string['createnewruleset'] = 'Neuen Regelsatz erstellen';
$string['rulesetname'] = 'Name des Regelsatzes';
$string['rulesetdescription'] = 'Beschreibung des Regelsatzes';
$string['rulename'] = 'Regelname';
$string['conditiongroup'] = 'Bedingungsgruppe';
$string['conditiontype'] = 'Bedingungstyp';
$string['target'] = 'Zielobjekt';
$string['moduletypeorname'] = 'Modultyp / Bezeichner';
$string['settingname'] = 'Einstellungsname';
$string['expectedvalue'] = 'Erwarteter Wert';
$string['comparisonoperator'] = 'Vergleichsoperator';
$string['hascontent_targetchild'] = 'Typ des Kindelements';
$string['addconditionchain'] = 'Bedingungsgruppe hinzufügen (UND/ODER)';
$string['addconditionsegment'] = 'Bedingungssegment hinzufügen';
$string['logicaloperator'] = 'Logik zur nächsten Gruppe';
$string['failureactions'] = 'Aktionen bei Fehlschlag';
$string['failurehint'] = 'Hinweistext bei Fehlschlag';
$string['addaction'] = 'Aktion hinzufügen';
$string['actiontype'] = 'Aktionstyp';
$string['actionbuttonlabel'] = 'Button-Beschriftung';
$string['actionchangesetting'] = 'Zu ändernde Einstellung';
$string['actionnewvalue'] = 'Neuer Wert für Einstellung';
$string['actionaddcontenttype'] = 'Hinzuzufügender Inhaltstyp';
$string['initialsettingsjson'] = 'Initiale Einstellungen (JSON)';

// Rule form options
$string['target_course'] = 'Kurs';
$string['target_section'] = 'Abschnitt';
$string['target_module'] = 'Aktivität/Material (Mod)';
$string['target_subelement'] = 'Unterelement (z.B. Testfrage)';

$string['checktype_hascontent'] = 'Enthält Inhalt';
$string['checktype_nothascontent'] = 'Enthält NICHT Inhalt';
$string['checktype_hassetting'] = 'Hat Einstellung';
$string['checktype_nothassetting'] = 'Hat NICHT Einstellung';

$string['operator_equals'] = 'ist gleich';
$string['operator_notequals'] = 'ist nicht gleich';
$string['operator_contains'] = 'enthält';
$string['operator_notcontains'] = 'enthält nicht';
$string['operator_regex'] = 'entspricht Regex';
$string['operator_notregex'] = 'entspricht nicht Regex';
$string['operator_greaterthan'] = 'ist größer als';
$string['operator_lessthan'] = 'ist kleiner als';
$string['operator_greaterthanequals'] = 'ist größer als oder gleich';
$string['operator_lessthanequals'] = 'ist kleiner als oder gleich';
$string['operator_istrue'] = 'ist wahr';
$string['operator_isfalse'] = 'ist falsch';
$string['operator_isempty'] = 'ist leer';
$string['operator_isnotempty'] = 'ist nicht leer';
$string['operator_startswith'] = 'beginnt mit';
$string['operator_endswith'] = 'endet mit';

$string['actiontype_changesetting'] = 'Einstellung ändern';
$string['actiontype_addcontent'] = 'Inhalt hinzufügen';

// Feedback messages for manage_rules.php
$string['datasubmitted'] = 'Daten übermittelt (zum Debuggen)';
$string['errorsaving'] = 'Fehler beim Speichern der Regeldaten: ';
$string['changessaved'] = 'Änderungen gespeichert';

// Error messages from rule_manager & external (related to rule execution/definition)
$string['error_rule_not_found'] = 'Fehler: Regel mit ID {$a} nicht gefunden.';
$string['error_action_not_found'] = 'Fehler: Aktion mit ID {$a} nicht gefunden.';
$string['error_action_target_definition_not_found'] = 'Fehler: Zieldefinition der Aktion konnte nicht vollständig aufgelöst werden.';