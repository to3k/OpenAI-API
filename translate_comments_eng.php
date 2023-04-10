<?php
    // OPENAI API TOKEN
    $token = '[TOKEN_GOES_HERE]';
    
    // Check if a fragment has been sent for translation
    if(!empty($_POST['fragment']))
    {
        // If so, retrieve the variable in which it is stored
        $fragment = $_POST['fragment'];
        
        // Determine the maximum token limit that can be declared
        // Determine the length of the fragment
        $fragment_size = strlen($fragment);
        // For the gpt-3.5-turbo model, the token limit is 4096, so subtract the length calculated above from this limit
        $max_tokens = 4096-$fragment_size;

        // Array with information sent in the cURL request
        $postfields = array(
            "model" => "gpt-3.5-turbo", // Specify which model to use
            "messages" => [array(
                "role" => "user", // Specify the role of the message as coming from the user
                "content" => $fragment // Fragment to be translated
            )],
            "temperature" => 0.5, // Parameter that determines how creative (random) the response should be
            "max_tokens" => $max_tokens // Specify the maximum length of the response
        );
        // Convert the above array to a JSON object, as that is the format in which it must be sent
        $postfields = json_encode($postfields);

        // Request headers
        $headers = array(
            "Content-Type: application/json", // Specify the type of content being sent - JSON
            "Authorization: Bearer ".$token // Token for authentication when communicating with the API
        );
        
        // Initialize the cURL request
        $curl = curl_init();
        // Specify the URL to which the request is to be directed
        curl_setopt($curl, CURLOPT_URL, "https://api.openai.com/v1/chat/completions");
        // Instruct cURL to return the query result
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        // Declare that this is a POST request
        curl_setopt($curl, CURLOPT_POST, 1);
        // Define the data to be passed
        curl_setopt($curl, CURLOPT_POSTFIELDS, $postfields);
        // Set headers
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        // Send the request and save the decoded result to a variable
        $result = json_decode(curl_exec($curl), true);
        // Close the connection
        curl_close ($curl);
        // Retrieve the translated text from the result
        $translated = $result['choices']['0']['message']['content'];
        // Retrieve the length of the prompt (tokens) from the result
        $prompt_tokens = $result['usage']['prompt_tokens'];
        // Retrieve the length of the completion (tokens) from the result
        $completion_tokens = $result['usage']['completion_tokens'];
        // Retrieve the total number of tokens used from the result
        $total_tokens = $result['usage']['total_tokens'];
    }
    else
    {
        // If no fragment has been sent for translation, set the default value below
        $fragment = "Translate from Polish to English:\n";
        // PS: By changing the above value, the purpose of this script can be easily changed
    }
?>

<!-- HTML form -->
<form action="" method="POST" name="form">
    <!-- Text area for entering the text to be translated -->
    <textarea name="fragment" style="width: 100%; height: 40%;"><?php echo $fragment; ?></textarea>
    <br>
    <button type="submit">Translate</button>
</form>

<?php
    // If there is a translation
    if(!empty($translated))
    {
?>

    <!-- Display the translated text in a read-only text area -->
    Translated text:
    <textarea style="width: 100%; height: 40%;" disabled><?php echo $translated; ?></textarea>
    <br><br>

<?php
        // Calculate the cost of the translation
        $cost = 0.002 * $total_tokens / 1000;
        // Display the number of tokens used
        echo "Query tokens: ".$prompt_tokens." | Response tokens: ".$completion_tokens." | Total tokens: ".$total_tokens."<br>";
        // Display the cost of the translation
        echo "Cost (assuming pricing for gpt-3.5-turbo model = $0.002 / 1k tokens): $".$cost;
        echo "<br><br><hr>";
        // Display the API server response as an array
        echo "API server response:";
        echo "<pre>";
        print_r($result);
        echo "</pre>";
    }
?>
