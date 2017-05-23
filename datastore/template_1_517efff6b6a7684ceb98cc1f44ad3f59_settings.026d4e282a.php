<?php

return <<<'VALUE'
"namespace IPS\\Theme;\nclass class_forums_admin_settings extends \\IPS\\Theme\\Template\n{\n\tpublic $cache_key = '';\n\tfunction archiveRuleGtLt( $name, $value ) {\n\t\t$return = '';\n\t\t$return .= <<<CONTENT\n\n<select name=\"\nCONTENT;\n$return .= htmlspecialchars( $name, ENT_QUOTES | \\IPS\\HTMLENTITIES, 'UTF-8', FALSE );\n$return .= <<<CONTENT\n[0]\">\n\t<option value=\">\" \nCONTENT;\n\nif ( isset( $value[0] ) and $value[0] == '>' ):\n$return .= <<<CONTENT\nselected\nCONTENT;\n\nendif;\n$return .= <<<CONTENT\n>\nCONTENT;\n\n$return .= \\IPS\\Member::loggedIn()->language()->addToStack( htmlspecialchars( 'greater_than', \\IPS\\HTMLENTITIES, 'UTF-8', FALSE ), TRUE, array(  ) );\n$return .= <<<CONTENT\n<\/option>\n\t<option value=\"<\" \nCONTENT;\n\nif ( isset( $value[0] ) and $value[0] == '<' ):\n$return .= <<<CONTENT\nselected\nCONTENT;\n\nendif;\n$return .= <<<CONTENT\n>\nCONTENT;\n\n$return .= \\IPS\\Member::loggedIn()->language()->addToStack( htmlspecialchars( 'less_than', \\IPS\\HTMLENTITIES, 'UTF-8', FALSE ), TRUE, array(  ) );\n$return .= <<<CONTENT\n<\/option>\n<\/select>\n<input type=\"number\" name=\"\nCONTENT;\n$return .= htmlspecialchars( $name, ENT_QUOTES | \\IPS\\HTMLENTITIES, 'UTF-8', FALSE );\n$return .= <<<CONTENT\n[1]\" value=\"\nCONTENT;\n\nif ( isset( $value[1] ) ):\n$return .= <<<CONTENT\n\nCONTENT;\n$return .= htmlspecialchars( $value[1], ENT_QUOTES | \\IPS\\HTMLENTITIES, 'UTF-8', FALSE );\n$return .= <<<CONTENT\n\nCONTENT;\n\nendif;\n$return .= <<<CONTENT\n\" id='el\nCONTENT;\n$return .= htmlspecialchars( $name, ENT_QUOTES | \\IPS\\HTMLENTITIES, 'UTF-8', FALSE );\n$return .= <<<CONTENT\n_1' class=\"ipsField_short\"> \nCONTENT;\n\n$return .= \\IPS\\Member::loggedIn()->language()->addToStack( htmlspecialchars( 'or', \\IPS\\HTMLENTITIES, 'UTF-8', FALSE ), TRUE, array(  ) );\n$return .= <<<CONTENT\n\n<span class='ipsCustomInput'>\n\t<input type=\"checkbox\" name=\"\nCONTENT;\n$return .= htmlspecialchars( $name, ENT_QUOTES | \\IPS\\HTMLENTITIES, 'UTF-8', FALSE );\n$return .= <<<CONTENT\n[2]\" data-control=\"unlimited\" \nCONTENT;\n\nif ( isset( $value[2] ) and $value[2] ):\n$return .= <<<CONTENT\nchecked\nCONTENT;\n\nendif;\n$return .= <<<CONTENT\n>\n\t<span><\/span>\n<\/span> <label for='el\nCONTENT;\n$return .= htmlspecialchars( $name, ENT_QUOTES | \\IPS\\HTMLENTITIES, 'UTF-8', FALSE );\n$return .= <<<CONTENT\n_1' class='ipsField_unlimited'>\nCONTENT;\n\nif ( in_array( $name, array( 'archive_topic_post', 'archive_topic_view', 'archive_topic_rating', 'archive_not_topic_post', 'archive_not_topic_view', 'archive_not_topic_rating'  ) ) ):\n$return .= <<<CONTENT\n\nCONTENT;\n\n$return .= \\IPS\\Member::loggedIn()->language()->addToStack( htmlspecialchars( 'no_restriction', \\IPS\\HTMLENTITIES, 'UTF-8', FALSE ), TRUE, array(  ) );\n$return .= <<<CONTENT\n\nCONTENT;\n\nelse:\n$return .= <<<CONTENT\n\nCONTENT;\n\n$return .= \\IPS\\Member::loggedIn()->language()->addToStack( htmlspecialchars( 'any_time', \\IPS\\HTMLENTITIES, 'UTF-8', FALSE ), TRUE, array(  ) );\n$return .= <<<CONTENT\n\nCONTENT;\n\nendif;\n$return .= <<<CONTENT\n<\/label>\nCONTENT;\n\n\t\treturn $return;\n}\n\n\tfunction archiveRuleTime( $name, $value ) {\n\t\t$return = '';\n\t\t$return .= <<<CONTENT\n\n<select name=\"\nCONTENT;\n$return .= htmlspecialchars( $name, ENT_QUOTES | \\IPS\\HTMLENTITIES, 'UTF-8', FALSE );\n$return .= <<<CONTENT\n[0]\">\n\t<option value=\"<\" \nCONTENT;\n\nif ( isset( $value[0] ) and $value[0] == '<' ):\n$return .= <<<CONTENT\nselected\nCONTENT;\n\nendif;\n$return .= <<<CONTENT\n>\nCONTENT;\n\n$return .= \\IPS\\Member::loggedIn()->language()->addToStack( htmlspecialchars( 'greater_than', \\IPS\\HTMLENTITIES, 'UTF-8', FALSE ), TRUE, array(  ) );\n$return .= <<<CONTENT\n<\/option>\n\t<option value=\">\" \nCONTENT;\n\nif ( isset( $value[0] ) and $value[0] == '>' ):\n$return .= <<<CONTENT\nselected\nCONTENT;\n\nendif;\n$return .= <<<CONTENT\n>\nCONTENT;\n\n$return .= \\IPS\\Member::loggedIn()->language()->addToStack( htmlspecialchars( 'less_than', \\IPS\\HTMLENTITIES, 'UTF-8', FALSE ), TRUE, array(  ) );\n$return .= <<<CONTENT\n<\/option>\n<\/select>\n<input type=\"number\" name=\"\nCONTENT;\n$return .= htmlspecialchars( $name, ENT_QUOTES | \\IPS\\HTMLENTITIES, 'UTF-8', FALSE );\n$return .= <<<CONTENT\n[1]\" value=\"\nCONTENT;\n\nif ( isset( $value[1] ) ):\n$return .= <<<CONTENT\n\nCONTENT;\n$return .= htmlspecialchars( $value[1], ENT_QUOTES | \\IPS\\HTMLENTITIES, 'UTF-8', FALSE );\n$return .= <<<CONTENT\n\nCONTENT;\n\nendif;\n$return .= <<<CONTENT\n\" class=\"ipsField_short\" min=\"0\">\n<select name=\"\nCONTENT;\n$return .= htmlspecialchars( $name, ENT_QUOTES | \\IPS\\HTMLENTITIES, 'UTF-8', FALSE );\n$return .= <<<CONTENT\n[2]\">\n\t<option value=\"d\" \nCONTENT;\n\nif ( isset( $value[2] ) and $value[2] == 'd' ):\n$return .= <<<CONTENT\nselected\nCONTENT;\n\nendif;\n$return .= <<<CONTENT\n>\nCONTENT;\n\n$return .= \\IPS\\Member::loggedIn()->language()->addToStack( htmlspecialchars( 'days', \\IPS\\HTMLENTITIES, 'UTF-8', FALSE ), TRUE, array(  ) );\n$return .= <<<CONTENT\n<\/option>\n\t<option value=\"m\" \nCONTENT;\n\nif ( isset( $value[2] ) and $value[2] == 'm' ):\n$return .= <<<CONTENT\nselected\nCONTENT;\n\nendif;\n$return .= <<<CONTENT\n>\nCONTENT;\n\n$return .= \\IPS\\Member::loggedIn()->language()->addToStack( htmlspecialchars( 'months', \\IPS\\HTMLENTITIES, 'UTF-8', FALSE ), TRUE, array(  ) );\n$return .= <<<CONTENT\n<\/option>\n\t<option value=\"y\" \nCONTENT;\n\nif ( isset( $value[2] ) and $value[2] == 'y' ):\n$return .= <<<CONTENT\nselected\nCONTENT;\n\nendif;\n$return .= <<<CONTENT\n>\nCONTENT;\n\n$return .= \\IPS\\Member::loggedIn()->language()->addToStack( htmlspecialchars( 'years', \\IPS\\HTMLENTITIES, 'UTF-8', FALSE ), TRUE, array(  ) );\n$return .= <<<CONTENT\n<\/option>\n<\/select>\n\nCONTENT;\n\n$return .= \\IPS\\Member::loggedIn()->language()->addToStack( htmlspecialchars( 'ago', \\IPS\\HTMLENTITIES, 'UTF-8', FALSE ), TRUE, array(  ) );\n$return .= <<<CONTENT\n \nCONTENT;\n\n$return .= \\IPS\\Member::loggedIn()->language()->addToStack( htmlspecialchars( 'or', \\IPS\\HTMLENTITIES, 'UTF-8', FALSE ), TRUE, array(  ) );\n$return .= <<<CONTENT\n \n<span class='ipsCustomInput'>\n\t<input type=\"checkbox\" name=\"\nCONTENT;\n$return .= htmlspecialchars( $name, ENT_QUOTES | \\IPS\\HTMLENTITIES, 'UTF-8', FALSE );\n$return .= <<<CONTENT\n[3]\" id='el\nCONTENT;\n$return .= htmlspecialchars( $name, ENT_QUOTES | \\IPS\\HTMLENTITIES, 'UTF-8', FALSE );\n$return .= <<<CONTENT\n_3' data-control=\"unlimited\" \nCONTENT;\n\nif ( isset( $value[3] ) and $value[3] ):\n$return .= <<<CONTENT\nchecked\nCONTENT;\n\nendif;\n$return .= <<<CONTENT\n>\n\t<span><\/span>\n<\/span> <label for='el\nCONTENT;\n$return .= htmlspecialchars( $name, ENT_QUOTES | \\IPS\\HTMLENTITIES, 'UTF-8', FALSE );\n$return .= <<<CONTENT\n_3' class='ipsField_unlimited'>\nCONTENT;\n\nif ( mb_strpos( $name, 'not' ) ):\n$return .= <<<CONTENT\n\nCONTENT;\n\n$return .= \\IPS\\Member::loggedIn()->language()->addToStack( htmlspecialchars( 'no_restriction', \\IPS\\HTMLENTITIES, 'UTF-8', FALSE ), TRUE, array(  ) );\n$return .= <<<CONTENT\n\nCONTENT;\n\nelse:\n$return .= <<<CONTENT\n\nCONTENT;\n\n$return .= \\IPS\\Member::loggedIn()->language()->addToStack( htmlspecialchars( 'any_time', \\IPS\\HTMLENTITIES, 'UTF-8', FALSE ), TRUE, array(  ) );\n$return .= <<<CONTENT\n\nCONTENT;\n\nendif;\n$return .= <<<CONTENT\n<\/label>\nCONTENT;\n\n\t\treturn $return;\n}\n\n\tfunction archiveRules( $form, $totalTopics, $existingCount, $existingPercentage ) {\n\t\t$return = '';\n\t\t$return .= <<<CONTENT\n\n<div data-controller=\"forums.admin.settings.archiveRules\">\n\t<div class=\"ipsPad ipsType_normal\">\nCONTENT;\n\n$return .= \\IPS\\Member::loggedIn()->language()->addToStack( htmlspecialchars( 'archiving_blurb', \\IPS\\HTMLENTITIES, 'UTF-8', FALSE ), TRUE, array(  ) );\n$return .= <<<CONTENT\n<\/div>\n\t<div data-ipsSticky data-ipsSticky-spacing='60' class=\"ipsPad_half ipsAreaBackground_reset ipsType_center\">\n\t\t<strong>\nCONTENT;\n\n$return .= \\IPS\\Member::loggedIn()->language()->addToStack( htmlspecialchars( 'archive_rules_effects', \\IPS\\HTMLENTITIES, 'UTF-8', FALSE ), TRUE, array(  ) );\n$return .= <<<CONTENT\n<\/strong><br>\n\t\t<div class=\"ipsProgressBar\">\n\t\t\t<div class=\"ipsProgressBar_progress\" style=\"width:\nCONTENT;\n$return .= htmlspecialchars( $existingPercentage, ENT_QUOTES | \\IPS\\HTMLENTITIES, 'UTF-8', FALSE );\n$return .= <<<CONTENT\n%\" data-role=\"percentageBar\"><span data-role=\"percentage\">\nCONTENT;\n$return .= htmlspecialchars( $existingPercentage, ENT_QUOTES | \\IPS\\HTMLENTITIES, 'UTF-8', FALSE );\n$return .= <<<CONTENT\n<\/span>% (<span data-role=\"number\">\nCONTENT;\n$return .= htmlspecialchars( $existingCount, ENT_QUOTES | \\IPS\\HTMLENTITIES, 'UTF-8', FALSE );\n$return .= <<<CONTENT\n<\/span>\/\nCONTENT;\n$return .= htmlspecialchars( $totalTopics, ENT_QUOTES | \\IPS\\HTMLENTITIES, 'UTF-8', FALSE );\n$return .= <<<CONTENT\n)<\/div>\n\t\t<\/div>\n\t<\/div>\n\t{$form}\n<\/div>\nCONTENT;\n\n\t\treturn $return;\n}\n\n\tfunction popularNow( $name, $value ) {\n\t\t$return = '';\n\t\t$return .= <<<CONTENT\n\n<input type=\"number\" name=\"\nCONTENT;\n$return .= htmlspecialchars( $name, ENT_QUOTES | \\IPS\\HTMLENTITIES, 'UTF-8', FALSE );\n$return .= <<<CONTENT\n[posts]\" value=\"\nCONTENT;\n$return .= htmlspecialchars( $value['posts'], ENT_QUOTES | \\IPS\\HTMLENTITIES, 'UTF-8', FALSE );\n$return .= <<<CONTENT\n\" class=\"ipsField_short\"> \nCONTENT;\n\n$return .= \\IPS\\Member::loggedIn()->language()->addToStack( htmlspecialchars( 'posts_in_the_last', \\IPS\\HTMLENTITIES, 'UTF-8', FALSE ), TRUE, array(  ) );\n$return .= <<<CONTENT\n <input type=\"number\" name=\"\nCONTENT;\n$return .= htmlspecialchars( $name, ENT_QUOTES | \\IPS\\HTMLENTITIES, 'UTF-8', FALSE );\n$return .= <<<CONTENT\n[minutes]\" value=\"\nCONTENT;\n$return .= htmlspecialchars( $value['minutes'], ENT_QUOTES | \\IPS\\HTMLENTITIES, 'UTF-8', FALSE );\n$return .= <<<CONTENT\n\" class=\"ipsField_short\"> \nCONTENT;\n\n$return .= \\IPS\\Member::loggedIn()->language()->addToStack( htmlspecialchars( 'minutes', \\IPS\\HTMLENTITIES, 'UTF-8', FALSE ), TRUE, array(  ) );\n$return .= <<<CONTENT\n \nCONTENT;\n\n$return .= \\IPS\\Member::loggedIn()->language()->addToStack( htmlspecialchars( 'or', \\IPS\\HTMLENTITIES, 'UTF-8', FALSE ), TRUE, array(  ) );\n$return .= <<<CONTENT\n \n<span class='ipsCustomInput'>\n\t<input type=\"checkbox\" name=\"\nCONTENT;\n$return .= htmlspecialchars( $name, ENT_QUOTES | \\IPS\\HTMLENTITIES, 'UTF-8', FALSE );\n$return .= <<<CONTENT\n[never]\" id='el\nCONTENT;\n$return .= htmlspecialchars( $name, ENT_QUOTES | \\IPS\\HTMLENTITIES, 'UTF-8', FALSE );\n$return .= <<<CONTENT\n_never' data-control=\"unlimited\" \nCONTENT;\n\nif ( !$value['posts'] ):\n$return .= <<<CONTENT\nchecked\nCONTENT;\n\nendif;\n$return .= <<<CONTENT\n>\n\t<span><\/span>\n<\/span> <label for='el\nCONTENT;\n$return .= htmlspecialchars( $name, ENT_QUOTES | \\IPS\\HTMLENTITIES, 'UTF-8', FALSE );\n$return .= <<<CONTENT\n_never' class='ipsField_unlimited'>\nCONTENT;\n\n$return .= \\IPS\\Member::loggedIn()->language()->addToStack( htmlspecialchars( 'never', \\IPS\\HTMLENTITIES, 'UTF-8', FALSE ), TRUE, array(  ) );\n$return .= <<<CONTENT\n<\/label>\nCONTENT;\n\n\t\treturn $return;\n}}"
VALUE;
