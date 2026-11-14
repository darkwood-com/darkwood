export const darkwaarDecision = (intention, context) => {
  if (intention === 'explore') return { decision: 'Observer avant de transporter', plan: context === 'known' ? 'Confirmer la route sûre et les embranchements.' : 'Tester les cases, révéler les risques et noter le signal.', result: 'Une carte utile pour la prochaine tentative.', evaluation: 'Réussite si la connaissance acquise réduit l’incertitude.' };
  return context === 'known'
    ? { decision: 'Suivre la route reconnue', plan: 'Porter la braise en évitant la fissure déjà repérée.', result: 'La braise peut atteindre la sortie.', evaluation: 'Réussite si la livraison est accomplie dans les pas disponibles.' }
    : { decision: 'Reconnaître la rive d’abord', plan: 'Ne pas risquer la braise sur une route inconnue.', result: 'La livraison est différée au profit de l’exploration.', evaluation: 'L’intention n’est pas encore satisfaite, mais le risque est réduit.' };
};

export const heroclipDecision = (intention, context) => {
  const stories = {
    mystery: ['Une trace apparaît', 'Le personnage suit un indice', 'La porte révèle une nouvelle question'],
    reunion: ['Un souvenir est retrouvé', 'Deux trajectoires convergent', 'Les personnages se reconnaissent'],
    escape: ['Le danger se rapproche', 'Un passage est choisi', 'La sortie reste incertaine'],
  };
  const continuity = { strict: 'Conserver Nora, la palette ambre et la lanterne.', visual: 'Conserver la palette ambre ; varier personnage et accessoire.', free: 'Autoriser une nouvelle palette et de nouveaux repères.' };
  return { decision: stories[intention][0], plan: continuity[context], scenes: stories[intention], evaluation: context === 'strict' ? 'Continuité forte, variété visuelle réduite.' : context === 'visual' ? 'Identité visuelle stable, continuité narrative à relire.' : 'Exploration maximale, raccords à vérifier manuellement.' };
};

export const djDecision = (intention, duration) => {
  const base = {
    opening: { bpm: 138, levels: [2, 3, 3, 4, 4, 5, 5, 6], label: 'Installer les repères', transition: 'Entrée progressive, motif lisible, transition longue.' },
    build: { bpm: 155, levels: [3, 4, 5, 5, 6, 7, 8, 8], label: 'Augmenter la tension', transition: 'Densifier le motif puis raccourcir la transition.' },
    peak: { bpm: 170, levels: [7, 8, 9, 9, 10, 10, 9, 10], label: 'Maintenir le pic', transition: 'Impact immédiat, contraste bref, relance rapide.' },
  }[intention];
  const modifier = duration === 'short' ? -1 : duration === 'long' ? 1 : 0;
  return { ...base, levels: base.levels.map((n, i) => Math.max(1, Math.min(10, n + (i > 4 ? modifier : 0)))), evaluation: intention === 'peak' ? 'Le niveau d’énergie est élevé ; préserver une marge pour éviter la saturation.' : 'La progression laisse une direction claire à la séquence suivante.' };
};

export const flowDecision = (intention, work) => {
  if (intention === 'simple') return { decision: 'PHP inline', reason: 'La simplicité d’exploitation prime : un runtime, un chemin d’exécution direct et moins de supervision.', limit: work === 'io' ? 'Les attentes I/O indépendantes restent séquentielles.' : 'Les calculs restent sur le processus PHP ; aucun gain parallèle n’est promis.' };
  if (work === 'io') return { decision: 'Amp / Revolt (H1)', reason: 'Les opérations I/O indépendantes peuvent se chevaucher sur une event loop portable.', limit: 'Le code et les bibliothèques doivent être suspendables ; cela ne crée pas de parallélisme CPU.' };
  return { decision: 'Mesurer des workers ou kernels natifs', reason: 'Une event loop n’accélère pas un calcul CPU intensif. Isoler le travail permet d’étudier un parallélisme réel.', limit: 'Piste conditionnelle : coût de transfert, supervision et gains doivent être mesurés avant adoption.' };
};

export const uniflowDecision = (intention, context) => {
  const steps = intention === 'register' ? ['Collecter les informations', 'Valider les règles métier', 'Préparer la confirmation'] : ['Collecter les lignes', 'Valider disponibilité et total', 'Préparer la commande'];
  if (context === 'missing') return { decision: 'Suspendre avant effet', steps: steps.slice(0, 2), evaluation: 'Résultat incomplet : demander l’information obligatoire sans prétendre que le processus est terminé.' };
  return { decision: 'Définir la séquence', steps, evaluation: 'La définition est prête ; elle ne constitue pas encore une exécution durable ni un paiement.' };
};
