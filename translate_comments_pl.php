<?php
    // OPENAI API TOKEN
    $token = '[TUTAJ_WKLEJ_TOKEN]';
    
    // Sprawdza czy wysłano fragment do tłumaczenia
    if(!empty($_POST['fragment']))
    {
        // Jeżeli tak to pobiera zmienną POST, w której jest przechowywany
        $fragment = $_POST['fragment'];
        
        // Określa jaki maksymalny limit tokenów możemy zadeklarować
        // Określa ilość znaków fragmentu (długość)
        $fragment_size = strlen($fragment);
        // Dla modelu gpt-3.5-turbo limit tokenów to 4096, więc odejmujemy wyżej wyliczoną długość od tego limitu
        $max_tokens = 4096-$fragment_size;

        // Tablica z informacjami wysyłanymi w zapytaniu cURL
        $postfields = array(
            "model" => "gpt-3.5-turbo", // Określenie jaki model ma zostać użyty
            "messages" => [array(
                "role" => "user", // Określenie roli wiadomości jako ta od użytkownika
                "content" => $fragment // Fragment do tłumaczenia
            )],
            "temperature" => 0.5, // Parametr, który określa jak kreatywna (losowa) ma być odpowiedź
            "max_tokens" => $max_tokens // Określenie jak długa może być odpowiedź
        );
        // Konwertuje powyższą tablicę w obiekt JSON, bo w takiej formie należy go wysłać
        $postfields = json_encode($postfields);

        // Nagłówki zapytania
        $headers = array(
            "Content-Type: application/json", // Określenie typu wysyłanej treści - JSON
            "Authorization: Bearer ".$token // Token do uwierzytelnienia przy komunikacji z API
        );
        
        // Inicjalizuje zapytanie cURL
        $curl = curl_init();
        // Określa URL do którego ma zostać skierowane zapytanie
        curl_setopt($curl, CURLOPT_URL, "https://api.openai.com/v1/chat/completions");
        // Nakazuje cURL zwrócić wynik zapytania
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        // Deklaruje, że ma to być zapytanie typu POST
        curl_setopt($curl, CURLOPT_POST, 1);
        // Definiuje dane do przekazania
        curl_setopt($curl, CURLOPT_POSTFIELDS, $postfields);
        // Ustawia nagłówki
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        // Wysyła zapytanie i zapisuje zdekodowany wynik do zmiennej
        $result = json_decode(curl_exec($curl), true);
        // Zamyka połączenie
        curl_close ($curl);
        // Wyciąga z wyniku przetłumaczoną treść
        $translated = $result['choices']['0']['message']['content'];
        // Wyciąga z wyniku długość zapytania (tokeny)
        $prompt_tokens = $result['usage']['prompt_tokens'];
        // Wyciąga z wyniku długość odpowiedzi (tokeny)
        $completion_tokens = $result['usage']['completion_tokens'];
        // Wyciąga z wyniku całkowitą ilość użytych tokenów
        $total_tokens = $result['usage']['total_tokens'];
    }
    else
    {
        // Jeżeli nie wysłano fragmentu do tłumaczenia to ustawia poniższą wartość domyślną
        $fragment = "Przetłumacz z polskiego na angielski:\n";
        // PS: Poprzez zmianę powyższej wartości można łatwo zmienić przeznaczenie tego skryptu
    }
?>

<!-- Formularz HTML -->
<form action="" method="POST" name="form">
    <!-- Pole tekstowe do wpisania fragmentu do przetłumaczenia -->
    <textarea name="fragment" style="width: 100%; height: 40%;"><?php echo $fragment; ?></textarea>
    <br>
    <button type="submit">Tłumacz</button>
</form>

<?php
    // Jeżeli istnieje tłumaczenie
    if(!empty($translated))
    {
?>

        <!-- Wyświetla przetłumaczony fragment w polu tekstowym z wyłączoną moliwością edycji -->
        Przetłumaczony tekst:
        <textarea style="width: 100%; height: 40%;" disabled><?php echo $translated; ?></textarea>
        <br><br>

<?php
        // Oblicza koszt wykonania zadania
        $cost = 0.002 * $total_tokens / 1000;
        // Wyświetla ilość użytych tokenów
        echo "Tokeny zapytania: ".$prompt_tokens." | Tokeny odpowiedzi: ".$completion_tokens." | Tokeny ogółem: ".$total_tokens."<br>";
        // Wyświetla koszt wykonania zadania
        echo "Koszt (przy założeniu cennika dla modelu gpt-3.5-turbo = $0.002 / 1k tokenów): $".$cost;
        echo "<br><br><hr>";
        // Wyświetla odpowiedź od serwera API przekonwertowaną na tablicę
        echo "Odpowiedź serwera API:";
        echo "<pre>";
        print_r($result);
        echo "</pre>";
    }
?>
