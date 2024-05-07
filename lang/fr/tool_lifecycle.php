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
 * Life cycle language strings.
 *
 * @package tool_lifecycle
 * @copyright  2024 Pascal Burkhard
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['abortdisableworkflow'] = 'Désactiver le flux (interrompt les processus, peut-être dangereux !)';
$string['abortdisableworkflow_confirm'] = 'Le flux de travail va être désactivé et tous les processus en cours de ce flux de travail seront interrompus. Êtes-vous sûr de vous ?';
$string['abortprocesses'] = 'Abandonner les processus en cours (peut-être dangereux !)';
$string['abortprocesses_confirm'] = 'Tous les processus en cours de ce flux de travail seront interrompus. Êtes-vous sûr ?';
$string['activateworkflow'] = 'Activer';
$string['active'] = 'Actif';
$string['active_automatic_workflows_heading'] = 'Flux de travail automatiques actifs';
$string['active_manual_workflows_heading'] = 'Flux de travail manuels actifs';
$string['active_workflow_not_changeable'] = 'Le flux de travail a déjà été activé. Selon le type d\'étape, certains de ses paramètres peuvent être encore modifiables. Les modifications apportées aux déclencheurs n\'affecteront pas les cours déjà déclenchés.';
$string['active_workflow_not_removeable'] = 'Le flux de travail est actif. Il n\'est pas possible de le supprimer.';
$string['active_workflows_header'] = 'Flux de travail actifs';
$string['active_workflows_list'] = 'Liste des flux de travail actifs';
$string['add_new_step_instance'] = 'Ajouter une nouvelle étape...';
$string['add_new_trigger_instance'] = 'Ajouter un nouveau déclenchement...';
$string['add_workflow'] = 'Créer un nouveau flux de travail';
$string['adminsettings_edit_step_instance_heading'] = 'Étape pour le flux de travail \'{$a}\'';
$string['adminsettings_edit_trigger_instance_heading'] = 'Déclencheur du flux de travail \'{$a}\'';
$string['adminsettings_edit_workflow_definition_heading'] = 'Définition du flux de travail';
$string['adminsettings_heading'] = 'Paramètres du flux de travail';
$string['adminsettings_workflow_definition_steps_heading'] = 'Étapes du flux de travail';
$string['all_delays'] = 'Tous les reports';
$string['anonymous_user'] = 'Utilisateur anonyme';
$string['apply'] = 'Appliquer';
$string['backupcreated'] = 'Créé le';
$string['backupworkflow'] = 'Processus de sauvegarde';
$string['cachedef_mformdata'] = 'Mise en cache des données mform';
$string['cannot_trigger_workflow_manually'] = 'Le flux de travail demandé n\'a pas pu être déclenché manuellement.';
$string['config_backup_path'] = 'Chemin du dossier de sauvegarde du cycle de vie';
$string['config_backup_path_desc'] = 'Ce paramètre définit l\'emplacement de stockage des sauvegardes créées par l\'étape de sauvegarde.
Le chemin doit être spécifié comme un chemin absolu sur votre serveur.';
$string['config_delay_duration'] = 'Durée par défaut d\'un délai de cours';
$string['config_delay_duration_desc'] = 'Ce paramètre définit la durée du délai par défaut d\'un flux de travail
au cas où l\'un de ses processus serait annulé ou terminé.
La durée du délai détermine la durée pendant laquelle un cours ne sera pas traité à nouveau dans l\'un ou l\'autre cas.';
$string['config_showcoursecounts'] = 'Show amount of courses which will be triggered';
$string['config_showcoursecounts_desc'] = 'La page d\'aperçu du flux de travail affiche par défaut le nombre de cours qui seront déclenchés par les 
déclencheurs configurés, ce qui peut être lourd à charger. Désactivez cette option si vous rencontrez des problèmes de chargement de la vue d\'ensemble du flux de travail.';
$string['course_backups_list_header'] = 'Sauvegardes de cours';
$string['courseid'] = 'ID du cours';
$string['coursename'] = 'Nom du cours';
$string['courses_will_be_excluded'] = '{$a} cours seront exclus';
$string['courses_will_be_excluded_total'] = '{$a} cours seront exclus au total';
$string['courses_will_be_triggered'] = '{$a} cours seront déclenchés';
$string['courses_will_be_triggered_total'] = '{$a} cours seront déclenchés au total';
$string['create_copy'] = 'Créer une copie';
$string['create_step'] = 'Créer une étape';
$string['create_trigger'] = 'Créer un déclencheur';
$string['create_workflow_from_existing'] = 'Copier un nouveau flux de travail à partir d\'un flux existant';
$string['date'] = 'Date d\'échéance';
$string['deactivated'] = 'Désactivé';
$string['deactivated_workflows_list'] = 'Liste des flux de travail désactivés';
$string['deactivated_workflows_list_header'] = 'Flux de travail désactivés';
$string['delayed_courses_header'] = 'Cours différés';
$string['delayed_for_workflow_until'] = 'Différé pour "{$a->name}" jusqu\'à {$a->date}';
$string['delayed_for_workflows'] = 'Différé pour {$a} flux de travail';
$string['delayed_globally'] = 'Reporté globalement jusqu\'à {$a}';
$string['delayed_globally_and_seperately'] = 'Reporté globalement et séparément pour {$a} flux de travail';
$string['delayed_globally_and_seperately_for_one'] = 'Reporté globalement et séparément pour 1 flux de travail';
$string['delays'] = 'Reports';
$string['delays_for_workflow'] = 'Reports pour "{$a}"';
$string['delete_all_delays'] = 'Supprimer tous les reports';
$string['delete_delay'] = 'Supprimer le report';
$string['deleteworkflow'] = 'Supprimer le flux de travail';
$string['deleteworkflow_confirm'] = 'Le flux de travail va être supprimé. Cela ne peut pas être annulé. En êtes-vous sûr ?';
$string['details:displaytitle'] = 'Affiché aux enseignants en tant que <b>{$a}</b>.';
$string['details:finishdelay'] = 'Lorsqu\'un cours a terminé le flux de travail, il est reporté de <b>{$a}</b>.';
$string['details:globaldelay_no'] = 'Ces reports s\'appliquent <b>uniquement à ce flux de travail</b>.';
$string['details:globaldelay_yes'] = 'Ces reports s\'appliquent <b>à tous les flux de travail</b>.';
$string['details:rollbackdelay'] = 'Lorsqu\'un cours est repris, il est reporté de <b>{$a}</b>.';
$string['disableworkflow'] = 'Désactiver le flux de travail (les processus continuent à fonctionner)';
$string['disableworkflow_confirm'] = 'Le flux de travail va être désactivé. En êtes-vous sûr ?';
$string['download'] = 'Télécharger';
$string['draft'] = 'Brouillon';
$string['duplicateworkflow'] = 'Dupliquer le flux de travail';
$string['edit_step'] = 'Modifier l\'étape';
$string['edit_trigger'] = 'Modifier le déclencheur';
$string['editworkflow'] = 'Modifier les paramètres généraux';
$string['error_wrong_trigger_selected'] = 'Vous avez essayé de demander un déclencheur automatique.';
$string['errorbackuppath'] = "Erreur lors de la création du répertoire de sauvegarde. Il se peut que vous n'ayez pas l'autorisation de le faire.
Veuillez vérifier votre chemin d'accès dans Administration du site/Plugins/Outils d'administration/Cycle de vie/Général et sous-plugins/backup_path.";
$string['errornobackup'] = "Aucune sauvegarde n'a été créée dans le répertoire spécifié, pour des raisons inconnues.";
$string['find_course_list_header'] = 'Trouver des cours';
$string['followedby_none'] = 'Aucun';
$string['force_import'] = 'Essayez d\'ignorer les erreurs et importez quand même le flux de travail. <b>À vos risques et périls!</b>';
$string['forselected'] = 'Pour tous les processus sélectionnés';
$string['general_config_header'] = "Général et sous-plugins";
$string['general_settings_header'] = 'Paramètres généraux';
$string['globally'] = 'Reports globaux';
$string['globally_until_date'] = 'Globalement jusqu\'au {$a}';
$string['interaction_success'] = 'L\'action a été sauvegardée avec succès.';
$string['invalid_workflow'] = 'Configuration invalide du flux de travail';
$string['invalid_workflow_cannot_be_activated'] = 'La définition du flux de travail n\'est pas valide et ne peut donc pas être activée.';
$string['invalid_workflow_details'] = 'Passez à la vue détaillée pour créer un déclencheur pour ce flux de travail';
$string['lastaction'] = 'Dernière action le';
$string['lifecycle:managecourses'] = 'Peut gérer des cours dans tool_lifecycle';
$string['lifecycle_cleanup_task'] = 'Supprimer les anciennes entrées de délai pour les flux de travail du cycle de vie';
$string['lifecycle_error_notify_task'] = 'Notifier l\'administrateur en cas d\'erreurs dans les processus du cycle de vie de l\'outil.';
$string['lifecycle_task'] = 'Exécuter les processus du cycle de vie';
$string['lifecyclestep'] = 'Étape du processus';
$string['lifecycletrigger'] = 'Déclencheur';
$string['managecourses_link'] = 'Gérer les cours';
$string['manual_trigger_process_existed'] = 'Un flux de travail pour ce cours existe déjà.';
$string['manual_trigger_success'] = 'Le flux de travail a démarré avec succès.';
$string['move_down'] = 'Descendre';
$string['move_up'] = 'Monter';
$string['name_until_date'] = '"{$a->name}" jusqu\'à {$a->date}';
$string['nocoursestodisplay'] = 'Il n\'y a actuellement aucun cours qui requiert votre attention !';
$string['nointeractioninterface'] = 'Pas d\'interface d\'interaction disponible !';
$string['noprocesserrors'] = 'Il n\'y a pas d\'erreurs de processus à gérer !';
$string['noprocessfound'] = 'Un processus avec l\'identifiant de processus donné n\'a pas pu être trouvé !';
$string['noremainingcoursestodisplay'] = 'Il n\'y a actuellement plus de cours disponibles !';
$string['nostepfound'] = 'Une étape avec l\'identifiant donné n\'a pas pu être trouvée !';
$string['notifyerrorsemailcontent'] = 'Il y a {$a->amount} nouvelles erreurs du processus tool_lifecycle qui attendent d\'être corrigées!' . "\n" . 'Veuillez les consulter sur {$a->url}.';
$string['notifyerrorsemailcontenthtml'] = 'Il y a {$a->nombre} nouvelles erreurs dans le processus tool_lifecycle qui attendent d\'être corrigées!<br>Veuillez les consulter dans la <a href="{$a->url}">vue d\'ensemble de la gestion des erreurs</a>.';
$string['notifyerrorsemailsubject'] = 'Il y a {$a->amount} nouvelles erreurs dans le processus tool_lifecycle qui attendent d\'être corrigées !';
$string['overview:add_trigger'] = 'Ajouter un déclencheur';
$string['overview:add_trigger_help'] = 'Vous ne pouvez ajouter qu\'une seule instance de chaque type de déclencheur.';
$string['overview:trigger'] = 'Déclencheur';
$string['overview:trigger_help'] = 'Un cours ne déclenche un flux de travail que si tous les déclencheurs sont d\'accord (opération ET).<br><br>
Les cours qui sont reportés ou qui se trouvent déjà dans un autre flux de travail ne sont pas inclus dans les chiffres affichés.<br>
Toutefois, ces chiffres ne sont que des approximations, car il se peut qu\'un cours soit exclu par un autre flux de travail, ou qu\'il déclenche un autre flux de travail avant celui-ci.';
$string['pluginname'] = 'Cycle de vie';
$string['plugintitle'] = 'Cycle de vie du cours';
$string['privacy:metadata:tool_lifecycle_action_log'] = 'Un registre des actions effectuées par les responsables de cours.';
$string['privacy:metadata:tool_lifecycle_action_log:action'] = 'Identifiant de l\'action effectuée.';
$string['privacy:metadata:tool_lifecycle_action_log:courseid'] = 'ID du cours pour lequel l\'action a été effectuée.';
$string['privacy:metadata:tool_lifecycle_action_log:processid'] = 'ID du processus dans lequel l\'action a été effectuée.';
$string['privacy:metadata:tool_lifecycle_action_log:stepindex'] = 'Indice de l\'étape du flux de travail pour laquelle l\'action a été effectuée.';
$string['privacy:metadata:tool_lifecycle_action_log:time'] = 'Heure à laquelle l\'action a été effectuée.';
$string['privacy:metadata:tool_lifecycle_action_log:userid'] = 'ID de l\'utilisateur qui a effectué l\'action.';
$string['privacy:metadata:tool_lifecycle_action_log:workflowid'] = 'ID du flux de travail dans lequel l\'action a été effectuée.';
$string['proceed'] = 'Procéder';
$string['process_errors_header'] = 'Gestion des erreurs';
$string['process_proceeded_event'] = 'Un processus a été mis en place';
$string['process_rollback_event'] = 'Un processus a été annulé';
$string['process_triggered_event'] = 'Un processus a été déclenché';
$string['restore'] = 'Restaurer';
$string['restore_error_in_step'] = 'Une erreur s\'est produite lors de l\'importation de l\'étape "{$a}" : ';
$string['restore_error_in_trigger'] = 'Une erreur s\'est produite lors de l\'importation du déclencheur "{$a}" : ';
$string['restore_step_does_not_exist'] = 'L\'étape {$a} n\'est pas installée, mais elle est incluse dans le fichier de sauvegarde. Veuillez d\'abord l\'installer et réessayer.';
$string['restore_subplugins_invalid'] = 'Format incorrect du fichier de sauvegarde. La structure des éléments du sous-plugin n\'est pas conforme aux attentes.';
$string['restore_trigger_does_not_exist'] = 'Le déclencheur {$a} n\'est pas installé, mais il est inclus dans le fichier de sauvegarde. Veuillez d\'abord l\'installer et réessayer.';
$string['restore_workflow_not_found'] = 'Format incorrect du fichier de sauvegarde. Le flux de travail n\'a pas pu être trouvé.';
$string['rollback'] = 'Retour en arrière';
$string['see_in_workflow'] = 'Voir dans le flux de travail';
$string['show_delays'] = 'Type de vue';
$string['status'] = 'Statut';
$string['step'] = 'Étape du processus';
$string['step_delete'] = 'Supprimer';
$string['step_edit'] = 'Editer';
$string['step_instancename'] = 'Nom de l\'instance';
$string['step_instancename_help'] = 'Titre de l\'instance d\'étape (visible uniquement pour les administrateurs).';
$string['step_settings_header'] = 'Paramètres spécifiques du type d\'étape';
$string['step_show'] = 'Afficher';
$string['step_sortindex'] = 'Monter/Descendre';
$string['step_subpluginname'] = 'Nom du sous-plugin';
$string['step_subpluginname_help'] = 'Titre du sous-plugin/déclencheur d\'étape (visible uniquement pour les administrateurs).';
$string['step_type'] = 'Type';
$string['subplugintype_lifecyclestep'] = 'Étape d\'un processus de cycle de vie';
$string['subplugintype_lifecyclestep_plural'] = 'Étapes d\'un processus de cycle de vie';
$string['subplugintype_lifecycletrigger'] = 'Déclenchement d\'un processus de cycle de vie';
$string['subplugintype_lifecycletrigger_plural'] = 'Déclencheurs du lancement d\'un processus de cycle de vie';
$string['tablecourseslog'] = 'Actions passées';
$string['tablecoursesremaining'] = 'Cours restants';
$string['tablecoursesrequiringattention'] = 'Cours qui requièrent votre attention';
$string['tools'] = 'Outils';
$string['trigger'] = 'Déclencheur';
$string['trigger_does_not_exist'] = 'Le déclencheur demandé n\'a pas pu être trouvé.';
$string['trigger_enabled'] = 'Activé';
$string['trigger_instancename'] = 'Nom de l\'instance';
$string['trigger_instancename_help'] = 'Titre de l\'instance de déclenchement (visible uniquement pour les administrateurs).';
$string['trigger_settings_header'] = 'Paramètres spécifiques du type de déclencheur';
$string['trigger_sortindex'] = 'Monter/Descendre';
$string['trigger_subpluginname'] = 'Nom du sous-plugin';
$string['trigger_subpluginname_help'] = 'Titre du sous-plugin/déclencheur d\'étape (visible uniquement pour les administrateurs).';
$string['trigger_workflow'] = 'Flux de travail';
$string['upload_workflow'] = 'Télécharger le flux de travail';
$string['viewheading'] = 'Gérer les cours';
$string['viewsteps'] = 'Visualiser les étapes du flux de travail';
$string['workflow'] = 'Flux de travail';
$string['workflow_active'] = 'Actif';
$string['workflow_definition_heading'] = 'Définitions du flux de travail';
$string['workflow_delayforallworkflows'] = 'Report pour tous les flux de travail ?';
$string['workflow_delayforallworkflows_help'] = 'Si cette option est cochée, les durées indiquées en haut ne reportent pas seulement l\'exécution de ce flux de travail pour un cours, mais aussi pour tous les autres flux de travail. Ainsi, tant que la durée n\'est pas écoulée, aucun processus ne peut être lancé pour le cours concerné.';
$string['workflow_displaytitle'] = 'Titre du flux de travail affiché';
$string['workflow_displaytitle_help'] = 'Ce titre est affiché aux utilisateurs lorsqu\'ils gèrent leurs cours.';
$string['workflow_drafts_header'] = 'Brouillons des flux de travail';
$string['workflow_drafts_list'] = 'Liste des brouillons de flux de travail';
$string['workflow_duplicate_title'] = '{$a} (Copie)';
$string['workflow_finishdelay'] = 'Report en cas d\'achèvement du cours';
$string['workflow_finishdelay_help'] = 'Si un cours a terminé une instance du processus de ce flux de travail, cette valeur décrit le délai avant qu\'un processus pour cette combinaison de cours et de flux de travail ne puisse être relancé.';
$string['workflow_is_running'] = 'Le flux de travail est en cours d\'exécution.';
$string['workflow_not_removeable'] = 'Il n\'est pas possible de supprimer cette instance de flux de travail. Peut-être a-t-elle encore des processus en cours ?';
$string['workflow_processes'] = 'Processus actifs';
$string['workflow_rollbackdelay'] = 'Report en cas de retour en arrière';
$string['workflow_rollbackdelay_help'] = 'Si un cours a été annulé dans une instance de processus de ce flux de travail, cette valeur décrit le temps nécessaire pour relancer un processus pour cette combinaison de cours et de flux de travail.';
$string['workflow_sortindex'] = 'Monter/Descendre';
$string['workflow_started'] = 'Le flux de travail a débuté.';
$string['workflow_timeactive'] = 'Actif depuis';
$string['workflow_timedeactive'] = 'Désactivé depuis';
$string['workflow_title'] = 'Titre';
$string['workflow_title_help'] = 'Titre du flux de travail (visible uniquement pour les administrateurs).';
$string['workflow_tools'] = 'Actions';
$string['workflow_trigger'] = 'Déclencheur du flux de travail';
$string['workflow_was_not_imported'] = 'Le flux de travail n\'a pas été importé !';
$string['workflownotfound'] = 'Le flux de travail avec l\'identifiant {$a} n\'a pas pu être trouvé';
$string['workflowoverview'] = 'Voir le flux de travail';
$string['workflowoverview_list_header'] = 'Détails des flux de travail';
