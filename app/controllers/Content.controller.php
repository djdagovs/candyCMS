<?php

/*
 * @link http://github.com/marcoraddatz/candyCMS
 * @author Marco Raddatz <http://marcoraddatz.com>
*/

require_once 'app/models/Content.model.php';

final class Content extends Main {
  public final function __init() {
    $this->_oModel = new Model_Content($this->_aRequest, $this->_aSession);
  }

  public final function show() {
    $this->_setTitle(LANG_GLOBAL_CONTENTMANAGER);

    if (empty($this->_iId)) {
      $this->_oSmarty->assign('content', $this->_oModel->getData());
      $this->_oSmarty->assign('lang_headline', LANG_GLOBAL_CONTENTMANAGER);

      $this->_oSmarty->template_dir = Helper::getTemplateDir('contents/overview');
      return $this->_oSmarty->fetch('contents/overview.tpl');
    }
    else {
      $this->_aData = $this->_oModel->getData($this->_iId);

      # create output
      $this->_oSmarty->assign('c', $this->_aData[$this->_iId]);

      # Quick hack for displaying title without html tags
      $sTitle = $this->_removeHighlight(Helper::removeSlahes($this->_aData[$this->_iId]['title']));

      $this->_setDescription($this->_aData[$this->_iId]['teaser']);
      $this->_setKeywords($this->_aData[$this->_iId]['keywords']);
      $this->_setTitle($sTitle);

      $this->_oSmarty->template_dir = Helper::getTemplateDir('contents/show');
      return $this->_oSmarty->fetch('contents/show.tpl');
    }
  }

  protected final function _showFormTemplate($bUpdate = true) {
    if ($bUpdate == true) {
      $this->_aData = $this->_oModel->getData($this->_iId, true);
      $this->_oSmarty->assign('_action_url_', '/content/update');
      $this->_oSmarty->assign('_formdata_', 'update_content');
      $this->_oSmarty->assign('c', $this->_aData);

      # Language
      $this->_oSmarty->assign('lang_headline', LANG_GLOBAL_UPDATE_ENTRY);
      $this->_oSmarty->assign('lang_submit', LANG_GLOBAL_UPDATE_ENTRY);

      $this->_setTitle(Helper::removeSlahes($this->_aData['title']));
    }
    # We create new content
    else {
			$sTitle = isset($this->_aRequest['title']) ?
              $this->_aRequest['title'] :
              '';

      $sTeaser = isset($this->_aRequest['teaser']) ?
              $this->_aRequest['teaser'] :
              '';

      $sKeywords = isset($this->_aRequest['keywords']) ?
              $this->_aRequest['keywords'] :
              '';

      $sContent = isset($this->_aRequest['content']) ?
              $this->_aRequest['content'] :
              '';

			$this->_oSmarty->assign('_action_url_', '/content/create');
			$this->_oSmarty->assign('_formdata_', 'create_content');
			$this->_oSmarty->assign('_request_id_', '');

			# Create title
			$aContent = array('title' => $sTitle, 'content' => $sContent, 'teaser' => $sTeaser, 'keywords' => $sKeywords);
			$this->_oSmarty->assign('c', $aContent);

			# Language
			$this->_oSmarty->assign('lang_headline', LANG_GLOBAL_CREATE_ENTRY);
			$this->_oSmarty->assign('lang_submit', LANG_GLOBAL_CREATE_ENTRY);

			# INFO: _setTitle comes from section helper
		}

    if (!empty($this->_aError)) {
      foreach ($this->_aError as $sField => $sMessage)
        $this->_oSmarty->assign('error_' . $sField, $sMessage);
    }

    $this->_oSmarty->assign('lang_create_keywords_info', LANG_CONTENT_INFO_KEYWORDS);
    $this->_oSmarty->assign('lang_create_teaser_info', LANG_CONTENT_INFO_TEASER);

    $this->_oSmarty->template_dir = Helper::getTemplateDir('contents/_form');
    return $this->_oSmarty->fetch('contents/_form.tpl');
  }

  protected final function _create() {
		if (!isset($this->_aRequest['title']) || empty($this->_aRequest['title']))
      $this->_aError['title'] = LANG_ERROR_FORM_MISSING_TITLE;

    if (!isset($this->_aRequest['content']) || empty($this->_aRequest['content']))
      $this->_aError['content'] = LANG_ERROR_FORM_MISSING_CONTENT;

    if (isset($this->_aError))
      return $this->_showFormTemplate(false);

    else {
      if ($this->_oModel->create() === true) {
        Log::insert($this->_aRequest['section'], $this->_aRequest['action'], Helper::getLastEntry('contents'));
        return Helper::successMessage(LANG_SUCCESS_CREATE, '/content');
      }
      else
        return Helper::errorMessage(LANG_ERROR_SQL_QUERY, '/content');
    }
	}

  protected final function _update() {
		$sRedirect = '/content/' . (int) $this->_aRequest['id'];
		if ($this->_oModel->update((int) $this->_aRequest['id']) === true) {
      Log::insert($this->_aRequest['section'], $this->_aRequest['action'],  (int) $this->_aRequest['id']);
			return Helper::successMessage(LANG_SUCCESS_UPDATE, $sRedirect);
    }
		else
			return Helper::errorMessage(LANG_ERROR_SQL_QUERY, $sRedirect);
	}

	protected final function _destroy() {
		if ($this->_oModel->destroy((int) $this->_aRequest['id']) === true) {
      Log::insert($this->_aRequest['section'], $this->_aRequest['action'],  (int) $this->_aRequest['id']);
			return Helper::successMessage(LANG_SUCCESS_DESTROY, '/content');
    }
		else
			return Helper::errorMessage(LANG_ERROR_SQL_QUERY, '/content');
	}
}