/*jshint esversion: 6 */
/**
 * Remplissage auto du champ "date1_afficher" selon les valeurs
 * des champs "date1_debut" et "date1_fin".
 */
(() => {
  'use strict';
  const NAME_START = 'Infobox adresse[date1_début]';
  const NAME_END = 'Infobox adresse[date1_fin]';
  const NAME_RESULT = 'Infobox adresse[date1_afficher]';

  const getInputs = () => {
    return document.querySelectorAll(`input[name="${NAME_START}"], input[name="${NAME_END}"]`);
  };

  const generateString = () => {
    const inputs = getInputs();
    const values = [];
    inputs.forEach(input => {
      if (input.value !== '') {
        values.push(input.value);
      }
    });
    return values.join(' à ');
  };

  const onFieldChange = () => {
    const resultInput = document.querySelector(`input[name="${NAME_RESULT}"]`);
    if (resultInput) {
      resultInput.value = generateString();
    }
  };

  const initListeners = () => {
    getInputs().forEach(input => {
      input.addEventListener('change', onFieldChange);
    });
  };

  initListeners();
})();
