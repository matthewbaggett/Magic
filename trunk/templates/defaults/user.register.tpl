<form action="" method="POST">
   <div class="formrow">
      <label for="username">{t}Username{/t}:</label>
      <input name="username" id="username" type="text" value="{$page->form_values['username']}"/>
   </div>
   <div class="formrow">
      <label for="email">{t}Email{/t}:</label>
      <input name="email" id="email" type="text" value="{$page->form_values['email']}"/>
   </div>
   <div class="formrow">
      <label for="firstname">{t}Firstname{/t}:</label>
      <input name="firstname" id="firstname" type="text" value="{$page->form_values['firstname']}"/>
   </div>
   <div class="formrow">
      <label for="surname">{t}Surname{/t}:</label>
      <input name="surname" id="surname" type="text" value="{$page->form_values['surname']}"/>
   </div>
   <div class="formrow">
      <label for="password">{t}Password{/t}:</label>
      <input name="password" id="password" type="password" value="{$page->form_values['password']}"/>
   </div>
   <div class="formrow">
      <label for="password2">{t}Password again{/t}:</label>
      <input name="password2" id="password2" type="password" value="{$page->form_values['password2']}"/>
   </div>
   <div class="formrow buttonrow">
      <input type="submit" value="{t nodfn=true}Register{/t}"/>
   </div>
</form>