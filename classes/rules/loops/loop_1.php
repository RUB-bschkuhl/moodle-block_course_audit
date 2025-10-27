<!--Loop 1 should do the following
        -> Sind mindestens 3 Elemente in Sektion vorhanden?
        -> Ist ein Element Quiz
        -> Die anderen Wissensaufbau?
        -> Hat Quiz Vorbedingung?
        -> Hat eines der Elemente das Quiz als VOrbedingung
        -> Gibt es mehr als ein Element mit Quiz als Vorbedingung abh von Bestanden / nicht bestanden

        Dazu muss folgendes zuvor gemacht werden:
        Selektieren einer Sektion oder mehrerer Sektionen im Frontend des Kurses
        hints und actions sollten parameter annehmen können, um die Überprüfung zu konfigurieren
            mods einordnen in wissensaufbau und wissensüberprüfung

            bspw section_has([mod_quiz, mod_xyz, ...], allow_more = true) also section_has(array of must_haves, allow_empty = true, allow_more = true)
            oder section_has([MOD_WISSENSAUFBAU, MOD_WISSENÜBERPRÜFUNG], allow_more = true)




            TODO create class to sort all core mods into wissensaufbau and wissensüberprüfung

-->