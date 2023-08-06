function ExecuteScript() {
    // Get Input Values
    let userName = "staylor@jetrord.com";
    let userPassword = "scottMan8265";
    let error = true;
     // Get the input fields
     let usernameField = <input type="text" class="mdc-text-field__input" aria-labelledby="username-label" id="username" name="username" size="25" tabindex="1" accesskey="u" autocomplete="off" value="" />;
     let passwordField = <input type="password" id="password" name="password" size="25" tabindex="2" accesskey="p" autocomplete="off" class="mdc-text-field__input" aria-labelledby="password-label" value="" />;
     // Get the submit button
     let submitButton = <button class="mitra-primary-button mdc-button mdc-button--raised" name="submit" accesskey="l" value="Log in" tabindex="6" type="submit">
         <div class="mdc-button__ripple"></div>
         <span class="mdc-button__label">Log in</span>
     </button>;
 
     if (usernameField && passwordField && submitButton && (usernameField.value == "" || usernameField == "scriptjet@gmail.com") && passwordField.value == "") {
         // Fill the fields
         console.log("Filling the fields...");
         error = false;
         usernameField.value = userName;
         passwordField.value = userPassword;
     } else {
         console.log("One or more elements not found on the page.");
     }
 }