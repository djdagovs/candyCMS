<?php

/**
 * Handle all blog SQL requests.
 *
 * @link http://github.com/marcoraddatz/candyCMS
 * @author Marco Raddatz <http://marcoraddatz.com>
 * @license MIT
 * @since 1.0
 */

namespace CandyCMS\Model;

use CandyCMS\Helper\AdvancedException as AdvancedException;
use CandyCMS\Helper\Helper as Helper;
use CandyCMS\Helper\Page as Page;
use PDO;

require_once 'app/helpers/Page.helper.php';

class Blog extends Main {

  /**
   * Set blog entry or blog overview data.
   *
   * @access private
   * @param boolean $bUpdate prepare data for update
   * @param integer $iLimit blog post limit
   * @return array data
   *
   */
  private function _setData($bUpdate, $iLimit) {
    if (empty($this->_iId)) {

      # Show unpublished items to moderators or administrators only
      $sWhere = !isset($this->_aSession['userdata']['role']) ||
              isset($this->_aSession['userdata']['role']) && $this->_aSession['userdata']['role'] < 3 ?
              "WHERE published = '1'" :
              '';

      # Search blog for tags
      if (isset($this->_aRequest['search']) && !empty($this->_aRequest['search']) && empty($this->_aRequest['page'])) {
        $sWhere .= isset($sWhere) && !empty($sWhere) ? ' AND ' : ' WHERE ';
        $sWhere .= "tags LIKE '%" . Helper::formatInput($this->_aRequest['search']) . "%'";
      }

      # Count entries for pagination
      try {
        $oQuery = $this->_oDb->query("SELECT COUNT(*) FROM " . SQL_PREFIX . "blogs " . $sWhere);
        $iResult = $oQuery->fetchColumn();
      }
      catch (\PDOException $p) {
        AdvancedException::reportBoth('0043 - ' . $p->getMessage());
        exit('SQL error.');
      }

      $this->oPage = new Page($this->_aRequest, (int)$iResult, $iLimit);

      try {
        $oQuery = $this->_oDb->query("SELECT
                                        b.*,
                                        u.id AS uid,
                                        u.name,
                                        u.surname,
                                        u.email,
                                        u.use_gravatar,
                                        COUNT(c.id) AS comment_sum
                                      FROM
                                        " . SQL_PREFIX . "blogs b
                                      LEFT JOIN
                                        " . SQL_PREFIX . "users u
                                      ON
                                        b.author_id=u.id
                                      LEFT JOIN
                                        " . SQL_PREFIX . "comments c
                                      ON
                                        c.parent_id=b.id
                                        " . $sWhere . "
                                      GROUP BY
                                        b.id
                                      ORDER BY
                                        b.date DESC
                                      LIMIT
                                        " . $this->oPage->getOffset() . ",
                                        " . $this->oPage->getLimit());

        $aResult = & $oQuery->fetchAll(PDO::FETCH_ASSOC);
      }
      catch (\PDOException $p) {
        AdvancedException::reportBoth('0001 - ' . $p->getMessage());
        exit('SQL error.');
      }

      foreach ($aResult as $aRow) {
        # We use the date as identifier to give plugins the possibility to patch into the system.
        $iDate = $aRow['date'];

        $this->_aData[$iDate] = $this->_formatForOutput($aRow, 'blog');
        $this->_aData[$iDate]['tags']           = explode(', ', $aRow['tags']);
        $this->_aData[$iDate]['tags_raw']       = $aRow['tags'];
        $this->_aData[$iDate]['date_modified']  = !empty($aRow['date_modified']) ?
                Helper::formatTimestamp($aRow['date_modified']) :
                '';
      }
    }
    else {
      # Show unpublished items to moderators or administrators only
      $iPublished = $this->_aSession['userdata']['role'] > 3 ? 0 : 1;

      try {
        $oQuery = $this->_oDb->prepare("SELECT
                                          b.*,
                                          u.id AS uid,
                                          u.name,
                                          u.surname,
                                          u.email,
                                          u.use_gravatar,
                                          COUNT(c.id) AS comment_sum
                                        FROM
                                          " . SQL_PREFIX . "blogs b
                                        LEFT JOIN
                                          " . SQL_PREFIX . "users u
                                        ON
                                          b.author_id=u.id
                                        LEFT JOIN
                                          " . SQL_PREFIX . "comments c
                                        ON
                                          c.parent_id=b.id
                                        WHERE
                                          b.id = :id
                                        AND
                                          b.published >= :published
                                        LIMIT 1");

        $oQuery->bindParam('id', $this->_iId, PDO::PARAM_INT);
        $oQuery->bindParam('published', $iPublished, PDO::PARAM_INT);
        $oQuery->execute();

        $aRow = & $oQuery->fetch(PDO::FETCH_ASSOC);
      }
      catch (\PDOException $p) {
        AdvancedException::reportBoth('0002 - ' . $p->getMessage());
        exit('SQL error.');
      }

      if ($bUpdate == true)
        $this->_aData = $this->_formatForUpdate($aRow);

      else {
        $this->_aData[1] = $this->_formatForOutput($aRow, 'blog');
        $this->_aData[1]['tags'] = explode(', ', $aRow['tags']);
        $this->_aData[1]['tags_raw'] = $aRow['tags'];
        $this->_aData[1]['date_modified'] = !empty($aRow['date_modified']) ?
                Helper::formatTimestamp($aRow['date_modified']) :
                '';
      }
    }

    return $this->_aData;
  }

  /**
   * Get blog entry or blog overview data. Depends on avaiable ID.
   *
   * @access public
   * @param integer $iId ID to load data from. If empty, show overview.
   * @param boolean $bUpdate prepare data for update
   * @param integer $iLimit blog post limit
   * @return array data from _setData
   *
   */
  public function getData($iId = '', $bUpdate = false, $iLimit = LIMIT_BLOG) {
    $this->_iId = !empty($iId) ? $iId : $this->_iId;

    # Small fix for pagination
    if (isset($this->_aRequest['page']) && !empty($this->_aRequest['page']) &&
            isset($this->_aRequest['action']) && 'page' == $this->_aRequest['action'] &&
            !isset($this->_aRequest['parent_id']))
      $this->_iId = '';

    return $this->_setData($bUpdate, $iLimit);
  }

  /**
   * Create a blog entry.
   *
   * @access public
   * @return boolean status of query
   * @override app/models/Main.model.php
   *
   */
  public function create() {
    $this->_aRequest['published'] = isset($this->_aRequest['published']) ?
            (int) $this->_aRequest['published'] :
            0;

    try {
      $oQuery = $this->_oDb->prepare("INSERT INTO
                                        " . SQL_PREFIX . "blogs
                                        ( author_id,
                                          title,
                                          tags,
                                          teaser,
                                          keywords,
                                          content,
                                          date,
                                          published)
                                      VALUES
                                        ( :author_id,
                                          :title,
                                          :tags,
                                          :teaser,
                                          :keywords,
                                          :content,
                                          :date,
                                          :published )");

      $oQuery->bindParam('author_id', $this->_aSession['userdata']['id'], PDO::PARAM_INT);
      $oQuery->bindParam('title', Helper::formatInput($this->_aRequest['title'], false), PDO::PARAM_STR);
      $oQuery->bindParam('tags', Helper::formatInput($this->_aRequest['tags']), PDO::PARAM_STR);
      $oQuery->bindParam('teaser', Helper::formatInput($this->_aRequest['teaser'], false), PDO::PARAM_STR);
      $oQuery->bindParam('keywords', Helper::formatInput($this->_aRequest['keywords']), PDO::PARAM_STR);
      $oQuery->bindParam('content', Helper::formatInput($this->_aRequest['content'], false), PDO::PARAM_STR);
      $oQuery->bindParam('date', time(), PDO::PARAM_INT);
      $oQuery->bindParam('published', $this->_aRequest['published'], PDO::PARAM_INT);

      $bReturn = $oQuery->execute();
      parent::$iLastInsertId = Helper::getLastEntry('blogs');

      return $bReturn;
    }
    catch (\PDOException $p) {
      try {
        $this->_oDb->rollBack();
      }
      catch (\Exception $e) {
        AdvancedException::reportBoth('0003 - ' . $e->getMessage());
      }

      AdvancedException::reportBoth('0004 - ' . $p->getMessage());
      exit('SQL error.');
    }
  }

  /**
   * Update a blog entry.
   *
   * @access public
   * @param integer $iId ID to update
   * @return boolean status of query
   * @override app/models/Main.model.php
   *
   */
  public function update($iId) {
    $iDateModified = (isset($this->_aRequest['show_update']) && $this->_aRequest['show_update'] == true) ?
            time() :
            '';

    $iPublished = (isset($this->_aRequest['published']) && $this->_aRequest['published'] == true) ?
            '1' :
            '0';

    $iUpdateAuthor = (isset($this->_aRequest['show_update']) && $this->_aRequest['show_update'] == true) ?
            $this->_aSession['userdata']['id'] :
            (int) $this->_aRequest['author_id'];

    try {
      $oQuery = $this->_oDb->prepare("UPDATE
                                        " . SQL_PREFIX . "blogs
                                      SET
                                        author_id = :author_id,
                                        title = :title,
                                        tags = :tags,
                                        teaser = :teaser,
                                        keywords = :keywords,
                                        content = :content,
                                        date_modified = :date_modified,
                                        published = :published
                                      WHERE
                                        id = :id");

      $oQuery->bindParam('author_id', $iUpdateAuthor, PDO::PARAM_INT);
      $oQuery->bindParam('title', Helper::formatInput($this->_aRequest['title'], false), PDO::PARAM_STR);
      $oQuery->bindParam('tags', Helper::formatInput($this->_aRequest['tags']), PDO::PARAM_STR);
      $oQuery->bindParam('teaser', Helper::formatInput($this->_aRequest['teaser'], false), PDO::PARAM_STR);
      $oQuery->bindParam('keywords', Helper::formatInput($this->_aRequest['keywords']), PDO::PARAM_STR);
      $oQuery->bindParam('content', Helper::formatInput($this->_aRequest['content'], false), PDO::PARAM_STR);
      $oQuery->bindParam('date_modified', $iDateModified, PDO::PARAM_INT);
      $oQuery->bindParam('published', $iPublished, PDO::PARAM_INT);
      $oQuery->bindParam('id', $iId, PDO::PARAM_INT);

      return $oQuery->execute();
    }
    catch (\PDOException $p) {
      try {
        $this->_oDb->rollBack();
      }
      catch (\Exception $e) {
        AdvancedException::reportBoth('0005 - ' . $e->getMessage());
      }

      AdvancedException::reportBoth('0006 - ' . $p->getMessage());
      exit('SQL error.');
    }
  }

  /**
   * Delete a blog entry and also delete its comments.
   *
   * @access public
   * @param integer $iId ID to delete
   * @return boolean status of query
   * @override app/models/Main.model.php
   *
   */
  public function destroy($iId) {
    try {
      $oQuery = $this->_oDb->prepare("DELETE FROM
                                        " . SQL_PREFIX . "blogs
                                      WHERE
                                        id = :id
                                      LIMIT
                                        1");

      $oQuery->bindParam('id', $iId, PDO::PARAM_INT);
      $bResult = $oQuery->execute();
    }
    catch (\PDOException $p) {
      try {
        $this->_oDb->rollBack();
      }
      catch (\Exception $e) {
        AdvancedException::reportBoth('0007 - ' . $e->getMessage());
      }

      AdvancedException::reportBoth('0008 - ' . $p->getMessage());
      exit('SQL error.');
    }

    try {
      $oQuery = $this->_oDb->prepare("DELETE FROM
                                        " . SQL_PREFIX . "comments
                                      WHERE
                                        parent_id = :parent_id");

      $oQuery->bindParam('parent_id', $iId, PDO::PARAM_INT);
      $bResult = $oQuery->execute();
    }
    catch (\PDOException $p) {
      try {
        $this->_oDb->rollBack();
      }
      catch (\Exception $e) {
        AdvancedException::reportBoth('0009 - ' . $e->getMessage());
      }

      AdvancedException::reportBoth('0010 - ' . $p->getMessage());
      exit('SQL error.');
    }

    return $bResult;
  }
}