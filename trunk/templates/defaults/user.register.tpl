<form action="" method="POST">
   <div class="formrow">
      <label for="username">Username:</label>
      <input name="username" id="username" type="text" value="{$page->form_values['username']}"/>
   </div>
   <div class="formrow">
      <label for="email">Email:</label>
      <input name="email" id="email" type="text" value="{$page->form_values['email']}"/>
   </div>
   <div class="formrow">
      <label for="firstname">Firstname:</label>
      <input name="firstname" id="firstname" type="text" value="{$page->form_values['firstname']}"/>
   </div>
   <div class="formrow">
      <label for="surname">Surname:</label>
      <input name="surname" id="surname" type="text" value="{$page->form_values['surname']}"/>
   </div>
   <div class="formrow">
      <label for="password">Password:</label>
      <input name="password" id="password" type="password" value="{$page->form_values['password']}"/>
   </div>
   <div class="formrow">
      <label for="password2">Password again:</label>
      <input name="password2" id="password2" type="password" value="{$page->form_values['password2']}"/>
   </div>
   <div class="formrow buttonrow">
      <input type="submit" value="Register"/>
   </div>
</form>