<?php

class admin_setting_configtext_notify extends admin_setting_configtext {
    public function write_setting($data) {
        $oldvalue = $this->get_setting();
        $status = parent::write_setting($data);
        if ($status === '' && $oldvalue !== $data) {
            \local_myplugin\event\setting_updated::create([
                'context' => \context_system::instance(),
                'other' => [
                    'name' => $this->name,
                    'oldvalue' => $oldvalue,
                    'newvalue' => $data,
                ],
            ])->trigger();
        }
        return $status;
    }
}
