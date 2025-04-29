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

// Disclaimer and wiki related
$string['wiki_link'] = 'Dokumentations-Wiki öffnen';
$string['wiki_title'] = 'Kurs-Prüfer Dokumentation';
$string['wiki_heading'] = 'Konvertierungsrichtlinien & Best Practices';

// Rules
$string['rule_category_hint'] = 'Hinweis';
$string['rule_category_action'] = 'Aktion';

// Rules - PDF Only
$string['rule_pdf_only_name'] = 'Nur PDF-Ressourcen';
$string['rule_pdf_only_description'] = 'Prüft, ob ein Abschnitt nur PDF-Ressourcen enthält';
$string['rule_pdf_only_empty_section'] = 'Der Abschnitt ist leer. Bitte fügen Sie Ressourcen hinzu.';
$string['rule_pdf_only_non_pdf_resources'] = 'Der Abschnitt enthält Nicht-PDF-Ressourcen:';
$string['rule_pdf_only_non_pdf_resource_item'] = '- "{$a->name}" ({$a->type})';
$string['rule_pdf_only_success'] = 'Alle {$a->count} Ressourcen im Abschnitt sind PDFs.';

// Rules - Has Connections
$string['rule_has_connections_name'] = 'Aktivitätsverbindungen';
$string['rule_has_connections_description'] = 'Prüft, ob Aktivitäten in einem Abschnitt durch Abschlussbedingungen verbunden sind';
$string['rule_has_connections_empty_section'] = 'Der Abschnitt ist leer. Bitte fügen Sie Aktivitäten hinzu.';
$string['rule_has_connections_single_module'] = 'Der Abschnitt enthält nur eine Aktivität ("{$a->name}"). Mindestens zwei Aktivitäten sind erforderlich, um Verbindungen zu erstellen.';
$string['rule_has_connections_no_conditions'] = 'Keine Aktivitäten in diesem Abschnitt haben Abschlussbedingungen eingerichtet. Bitte fügen Sie Bedingungen hinzu, um einen Lernpfad zu erstellen.';
$string['rule_has_connections_success'] = '{$a->count} Aktivitäten haben Abschlussbedingungen eingerichtet.';
$string['rule_has_connections_module_with_condition'] = '- "{$a->name}" hat Abschlussbedingungen';
$string['rule_has_connections_some_without_conditions'] = '{$a->count} Aktivitäten haben keine Abschlussbedingungen:';
$string['rule_has_connections_module_without_condition'] = '- "{$a->name}" hat keine Abschlussbedingungen';

// Rules - Has Label - used in rule implementations
$string['rule_has_label_name'] = 'Textfeld vorhanden';
$string['rule_has_label_description'] = 'Prüft, ob ein Abschnitt ein Textfeld enthält';
$string['rule_has_label_empty_section'] = 'Der Abschnitt ist leer. Bitte fügen Sie Ressourcen hinzu.';
$string['rule_has_label_success'] = 'Der Abschnitt enthält ein Textfeld.';
$string['rule_has_label_failure'] = 'Der Abschnitt enthält kein Textfeld.';
$string['button_add_label'] = 'Textfeld hinzufügen';
$string['label_added_success'] = 'Textfeld erfolgreich hinzugefügt';
$string['label_added_failure'] = 'Textfeld konnte nicht hinzugefügt werden';
$string['label_intro'] = 'Nutzen Sie Textfelder, um erklärende Texte, Anweisungen oder Überschriften direkt in einem Kursabschnitt hinzuzufügen. Dies hilft dabei, Inhalte zu strukturieren und Lernende zu leiten.';
$string['label_name'] = 'Neues Textfeld';

// Summary related
$string['summary_heading'] = 'Zusammenfassung der Kurskonvertierung';
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