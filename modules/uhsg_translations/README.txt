UHSG Translations (fork of UHC Translations)

When adding new translations:

1. Add a new helper function to uhsg_translations.install with a
$translation_helper->addTranslation()-call for all added translations.

2. Create a new uhsg_translations_update_N hook implementation with a call to above
created helper function.

3. Also call the above added helper function in uhsg_translations_install to
make sure all translations would be applied even if this module was enabled on a
fresh db.

Overriding existing translations
By default existing translations are not overridden but it is possible by adding a
fourth argument TRUE in the $translation_helper->addTranslation()-call.
@see: Drupal\uhsg_translations\Helpers\TranslationHelper::addTranslation()


NOTE: Thanks to this module we can automatize translation imports and ignore
instructions in the old RELEASE.md file, such as:
"10. Jos `translations`-hakemiston `*.po`-tiedostot ovat muuttuneet uudessa
 asennuksessa, kirjaudu Drupaliin ja tuo uudet käännökset sisään
   `/admin/config/regional/translate/import`-polusta ("Overwrite non-customized
    translations" sekä "Overwrite existing customized translations" **päällä**).""
