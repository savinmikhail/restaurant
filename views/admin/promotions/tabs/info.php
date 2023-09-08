<?= $form->field($model, 'name', [
                                'options' => ['class' => 'form-group'],
                                'template' => '{beginWrapper}{input}{error}{endWrapper}',
                                'wrapperOptions' => ['class' => 'input-group mb-3'],
                            ])
                                ->label(false)
                                ->textInput(['placeholder' => $model->getAttributeLabel('Наименование')]); ?>

                            <?= $form->field($model, 'sort', [
                                'options' => ['class' => 'form-group'],
                                'template' => '{beginWrapper}{input}{error}{endWrapper}',
                                'wrapperOptions' => ['class' => 'input-group mb-3'],
                            ])
                                ->label(false)
                                ->textInput(['placeholder' => $model->getAttributeLabel('Сортировка')]); ?>


                            <?= $form->field($model, 'external_id', [
                                'options' => ['class' => 'form-group'],
                                'template' => '{beginWrapper}{input}{error}{endWrapper}',
                                'wrapperOptions' => ['class' => 'input-group mb-3'],
                            ])
                                ->label(false)
                                ->textInput(['placeholder' => $model->getAttributeLabel('Внешний ИД')]); ?>

                            <?= $form->field($model, 'active', [
                                'options' => ['class' => 'form-group'],
                                'template' => '{beginWrapper}{input}{error}{endWrapper}',
                                'wrapperOptions' => ['class' => 'mb-3'],
                            ])
                                ->label(false)
                                ->checkbox(['value' => 1, 'checked' => ($model->active == 1)])->label('Активность'); ?>

                            <?= $form->field($model, 'on_main', [
                                'options' => ['class' => 'form-group'],
                                'template' => '{beginWrapper}{input}{error}{endWrapper}',
                                'wrapperOptions' => ['class' => 'mb-3'],
                            ])
                                ->label(false)
                                ->checkbox(['value' => 1, 'checked' => ($model->on_main == 1)])->label('Отображать баннером на главной'); ?>
                            
                            <?= $form->field($model, 'only_new', [
                                'options' => ['class' => 'form-group'],
                                'template' => '{beginWrapper}{input}{error}{endWrapper}',
                                'wrapperOptions' => ['class' => 'mb-3'],
                            ])
                                ->label(false)
                                ->checkbox(['value' => 1, 'checked' => ($model->only_new == 1)])->label('Только для новых клиентов'); ?>
                            
                            <?= $form->field($model, 'notinapp', [
                                    'options' => ['class' => 'form-group'],
                                    'template' => '{beginWrapper}{input}{error}{endWrapper}',
                                    'wrapperOptions' => ['class' => 'mb-3'],
                                    ])
                                ->label(false)
                                ->checkbox(['value' => 1, 'checked' => ($model->notinapp == 1)])->label('Акция не для приложения'); ?>
                            <?= $form->field($model, 'publish', [
                                'options' => ['class' => 'form-group'],
                                'template' => '{beginWrapper}{input}{error}{endWrapper}',
                                'wrapperOptions' => ['class' => 'mb-3'],
                            ])
                                ->label(false)
                                ->checkbox(['value' => 1, 'checked' => ($model->publish == 1)])->label('Отображать в списке'); ?>
                            <?= $form->field($model, 'need_join', [
                                'options' => ['class' => 'form-group'],
                                'template' => '{beginWrapper}{input}{error}{endWrapper}',
                                'wrapperOptions' => ['class' => 'mb-3'],
                            ])
                                ->label(false)
                                ->checkbox(['value' => 1, 'checked' => ($model->need_join == 1)])->label('Активация при участии'); ?>

                            <?= $form->field($model, 'description', [
                                'options' => ['class' => 'form-group'],
                                'template' => '{beginWrapper}{input}{error}{endWrapper}',
                                'wrapperOptions' => ['class' => 'input-group mb-6'],
                            ])
                                ->label(false)
                                ->textarea(['placeholder' => $model->getAttributeLabel('Описание')]); ?>
                            
                            <label>Типы пользователей</label>
                    <?php $model->user_type = [];
               
                    if (is_array($promotionUserTypes)) {
                        foreach ($promotionUserTypes as $tu) {
                            $model->user_type[] = $tu['usertype_id'];
                        }
                    }
                    ?>
                    <?php
                    for ($user_type = 1; $user_type <= 3; ++$user_type) {?>
                        <?= $form->field($model, 'user_type['.$user_type.']', [
                        'options' => ['class' => 'form-group'],
                        'template' => '{label}{beginWrapper}{input}{error}{endWrapper}',
                        'wrapperOptions' => ['class' => 'mb-3'],
                    ])
                        ->checkbox(['value' => 1, 'checked' => (bool)in_array($user_type, $model->user_type)])->label(\app\models\tables\User::$user_types[$user_type]['name']); ?>
                        <?php
                    }?>
                            
                            <div class="input-group">
                            <?= $form->field($model, 'active_from', [
                                'options' => ['style'=>'width: 40%','class' => ''],
                                'template' => '{label} {beginWrapper}{input}{error}{endWrapper}',
                                'wrapperOptions' => ['class' => 'input-group mb-6'],
                            ])
                                ->label('Начало акции')
                                ->textInput(['type' => 'date']); ?>
                            &nbsp;&nbsp;
                            <?= $form->field($model, 'active_to', [
                                'options' => ['style'=>'width: 40%','class' => ''],
                                'template' => '{label} {beginWrapper}{input}{error}{endWrapper}',
                                'wrapperOptions' => ['class' => 'input-group mb-3'],
                            ])
                                ->label('Конец акии')
                                ->textInput(['type' => 'date']); ?>
                            </div>
                            <?= $form->field($model, 'period', [
                                'options' => ['class' => 'form-group'],
                                'template' => '{beginWrapper}{label}{input}{error}{endWrapper}',
                                'wrapperOptions' => ['class' => 'mb-3']
                            ])
                                ->dropdownList([
                                    'always'=>'Постоянно',
                                    'once'=>'Разово для покупателя',
                                    'period'=>'Раз в месяц',
                                    ])->label('Период') ?>
                            <?= $form->field($model, 'image', [
                                'options' => ['class' => 'form-group'],
                                'template' => '{label} {beginWrapper}{input}{error}{endWrapper}',
                                'wrapperOptions' => ['class' => 'input-group mb-3'],
                            ])
                                ->label('Изображение')
                                ->fileInput(['placeholder' => $model->getAttributeLabel('Изображение')]); ?>

                            <?php if ($model->image) { ?>
                                <img src="<?= $model->image; ?>" width="150" />
                                <?= $form->field($model, 'removeImage', [
                                    'options' => ['class' => 'form-group'],
                                    'template' => '{beginWrapper}{input}{error}{endWrapper}',
                                    'wrapperOptions' => ['class' => 'mb-3'],
                                ])
                                    ->label(false)
                                    ->checkbox(['value' => 1, 'checked' => false])->label('Удалить изображение'); ?>
                            <?php } ?>

                            <?= $form->field($model, 'detail_image', [
                                'options' => ['class' => 'form-group'],
                                'template' => '{label} {beginWrapper}{input}{error}{endWrapper}',
                                'wrapperOptions' => ['class' => 'input-group mb-3'],
                            ])
                                ->label('Детальное изображение')
                                ->fileInput(['placeholder' => $model->getAttributeLabel('Детальное изображение')]); ?>
                            <?php if ($model->detail_image) { ?>
                                <img src="<?= $model->detail_image; ?>" width="150" />
                                <?= $form->field($model, 'removeDetailImage', [
                                    'options' => ['class' => 'form-group'],
                                    'template' => '{beginWrapper}{input}{error}{endWrapper}',
                                    'wrapperOptions' => ['class' => 'mb-3'],
                                ])
                                    ->label(false)
                                    ->checkbox(['value' => 1, 'checked' => false])->label('Удалить детальное изображение'); ?>
                            <?php } ?>
                            <?= $form->field($model, 'banner', [
                                'options' => ['class' => 'form-group'],
                                'template' => '{label}: {beginWrapper}{input}{error}{endWrapper}',
                                'wrapperOptions' => ['class' => 'input-group mb-3'],
                            ])
                                ->label('Баннер на главной')
                                ->fileInput(['placeholder' => $model->getAttributeLabel('Баннер на главной')]); ?>

                            <?php if ($model->banner) { ?>
                                <img src="<?= $model->banner; ?>" width="150" />
                                <?= $form->field($model, 'removeBanner', [
                                    'options' => ['class' => 'form-group'],
                                    'template' => '{beginWrapper}{input}{error}{endWrapper}',
                                    'wrapperOptions' => ['class' => 'mb-3'],
                                ])
                                    ->label(false)
                                    ->checkbox(['value' => 1, 'checked' => false])->label('Удалить баннер'); ?>
                            <?php } ?>