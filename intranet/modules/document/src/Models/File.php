<?php

namespace Rikkei\Document\Models;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Document\View\DocConst;
use Storage;
use Rikkei\Magazine\Model\Magazine;

class File extends CoreModel
{
    protected $table = 'documentfiles';
    protected $fillable = ['name', 'url', 'content', 'type', 'mimetype', 'author_id', 'magazine_id'];

    public static function insertData($file = null, $fileUrl = null, $randName = true, $idMagazine = null)
    {
        $magazineId = null;
        if ($idMagazine) {
            $nameMagazine = Magazine::find($idMagazine);
            $magazine = self::where('magazine_id', $idMagazine)->first();
            if ($magazine) {
                $magazine->name = $nameMagazine->name;
                $magazine->save();
                return $fileModel = null;
            }
            $fileName = $nameMagazine->name;
            $fileUrl = '/document/view/file/'.$idMagazine;
            $mimeType = 'link';
            $fileType = 'link';
            $magazineId = $idMagazine;
        } elseif ($file) {
            //create folder upload
            DocConst::makeUploadDir();
            $uploadDir = trim(DocConst::UPLOAD_DIR, '/');
            $fileName = $file->getClientOriginalName();
            $fileUrl = self::checkRename($fileName, $randName);
            $mimeType = $file->getClientMimeType();
            $fileType = 'file';
            //move file upload
            Storage::disk('public')->put($uploadDir . '/' . $fileUrl, file_get_contents($file), 'public');
        } else {
            $fileName = $fileUrl;
            $mimeType = 'link';
            $fileType = 'link';
        }
        $fileModel = File::create([
            'name' => $fileName,
            'url' => $fileUrl,
            'mimetype' => $mimeType,
            'type' => $fileType,
            'author_id' => auth()->id(),
            'magazine_id' => $magazineId
        ]);
        return $fileModel;
    }

    /**
     * check and change name in folder upload
     * @param type $originalName
     * @return type
     */
    public static function checkRename($originalName, $randName = true)
    {
        $uploadDir = trim(DocConst::UPLOAD_DIR, '/');
        $arrName = explode('.', $originalName);
        $extension = array_pop($arrName);
        $name = str_slug(implode('.', $arrName));
        $reName = $name;
        if ($randName) {
            $reName = md5($name) . str_random();
        }
        $i = 1;
        while (Storage::disk('public')->exists($uploadDir . '/' . $reName . '.' . $extension)) {
            $reName = $name . '-' . $i;
            $i++;
        }
        return $reName . '.' . $extension;
    }

    /**
     * get file url
     * @return type
     */
    public function getSrc($checkExists = true)
    {
        return DocConst::getFileSrc($this->url, $checkExists, $this->type);
    }

    /**
     * delete file + unlink
     * @return type
     */
    public function delete()
    {
        $uploadDir = trim(DocConst::UPLOAD_DIR, '/');
        $path = $uploadDir . '/' . $this->url;
        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
        return parent::delete();
    }

    /*
     * delete list files by id
     */
    public static function deleteById($ids)
    {
        if (!is_array($ids)) {
            $ids = [$ids];
        }
        $files = self::whereIn('id', $ids)->get();
        if (!$files->isEmpty()) {
            foreach ($files as $file) {
                $file->delete();
            }
        }
    }

    public function downloadLink($docId)
    {
        if ($this->type == 'link') {
            return $this->url;
        }
        return route('doc::admin.download', ['docId' => $docId, 'id' => $this->id]);
    }

    public function frontDownloadLink($docId)
    {
        if ($this->type == 'link') {
            return $this->url;
        }
        return route('doc::file.download', ['docId' => $docId, 'fileId' => $this->id]);
    }
}
