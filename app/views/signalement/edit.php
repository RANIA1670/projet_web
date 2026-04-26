<!-- ========== SIGNALEMENT - EDIT ========== -->
<section class="page-header">
    <div class="container page-header-inner">
        <div class="breadcrumb">
            <a href="<?= APP_URL ?>/">Accueil</a>
            <i class="fas fa-chevron-right"></i>
            <a href="<?= APP_URL ?>/signalements">Signalements</a>
            <i class="fas fa-chevron-right"></i>
            <a href="<?= APP_URL ?>/signalement/<?= $signalement['id'] ?>">#<?= str_pad($signalement['id'],5,'0',STR_PAD_LEFT) ?></a>
            <i class="fas fa-chevron-right"></i>
            <span>Modifier</span>
        </div>
        <h1><i class="fas fa-edit" style="color:var(--secondary);"></i> Modifier le Signalement</h1>
        <p>Mettez à jour les informations du signalement #<?= str_pad($signalement['id'],5,'0',STR_PAD_LEFT) ?></p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div style="max-width:800px;margin:0 auto;">
            <div class="card" style="border-radius:var(--radius-xl);">
                <div class="card-body" style="padding:40px;">
                    <form action="<?= APP_URL ?>/signalement/<?= $signalement['id'] ?>/modifier" method="POST" enctype="multipart/form-data" id="editForm">

                        <div class="form-group">
                            <label class="form-label" for="editTitre">Titre <span class="required">*</span></label>
                            <div class="input-icon-wrap">
                                <i class="input-icon fas fa-heading"></i>
                                <input type="text" id="editTitre" name="titre" class="form-control" required
                                    value="<?= htmlspecialchars($signalement['titre']) ?>" maxlength="200">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="editDesc">Description <span class="required">*</span></label>
                            <textarea id="editDesc" name="description" class="form-control" required rows="5"><?= htmlspecialchars($signalement['description']) ?></textarea>
                        </div>

                        <div class="form-row form-row-2">
                            <div class="form-group">
                                <label class="form-label" for="editCat">Catégorie</label>
                                <div class="input-icon-wrap">
                                    <i class="input-icon fas fa-tag"></i>
                                    <select id="editCat" name="categorie_id" class="form-control">
                                        <option value="">-- Catégorie --</option>
                                        <?php foreach($categories as $cat): ?>
                                        <option value="<?= $cat['id'] ?>" <?= $signalement['categorie_id']==$cat['id']?'selected':'' ?>>
                                            <?= htmlspecialchars($cat['nom']) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="editPrio">Priorité</label>
                                <div class="input-icon-wrap">
                                    <i class="input-icon fas fa-flag"></i>
                                    <select id="editPrio" name="priorite" class="form-control">
                                        <?php foreach(['faible'=>'🟢 Faible','moyenne'=>'🟡 Moyenne','haute'=>'🟠 Haute','urgente'=>'🔴 Urgente'] as $v=>$l): ?>
                                        <option value="<?= $v ?>" <?= $signalement['priorite']===$v?'selected':'' ?>><?= $l ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-row form-row-2">
                            <div class="form-group">
                                <label class="form-label" for="editStatut">Statut</label>
                                <div class="input-icon-wrap">
                                    <i class="input-icon fas fa-info-circle"></i>
                                    <select id="editStatut" name="statut" class="form-control">
                                        <?php foreach(['nouveau'=>'Nouveau','en_attente'=>'En attente','en_cours'=>'En cours','resolu'=>'Résolu','ferme'=>'Fermé'] as $v=>$l): ?>
                                        <option value="<?= $v ?>" <?= $signalement['statut']===$v?'selected':'' ?>><?= $l ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="editDate">Date du problème</label>
                                <div class="input-icon-wrap">
                                    <i class="input-icon fas fa-calendar"></i>
                                    <input type="date" id="editDate" name="date_incident" class="form-control"
                                        value="<?= htmlspecialchars($signalement['date_incident']) ?>">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="editAdresse">Adresse</label>
                            <div class="input-icon-wrap">
                                <i class="input-icon fas fa-map-marker-alt"></i>
                                <input type="text" id="editAdresse" name="adresse" class="form-control"
                                    value="<?= htmlspecialchars($signalement['adresse']) ?>">
                            </div>
                        </div>

                        <?php if($signalement['image']): ?>
                        <div class="form-group">
                            <label class="form-label">Image actuelle</label>
                            <div style="display:flex;align-items:center;gap:16px;padding:16px;background:var(--gray-50);border-radius:var(--radius);border:1px solid var(--border-color);">
                                <img src="<?= UPLOAD_URL . htmlspecialchars($signalement['image']) ?>" style="width:80px;height:60px;object-fit:cover;border-radius:8px;" alt="">
                                <div>
                                    <p style="font-size:.82rem;font-weight:600;margin-bottom:4px;"><?= htmlspecialchars($signalement['image']) ?></p>
                                    <p style="font-size:.75rem;color:var(--text-muted);margin:0;">Téléchargez une nouvelle image pour remplacer</p>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <div class="form-group">
                            <label class="form-label">Nouvelle image (optionnel)</label>
                            <div class="file-upload" id="editFileZone">
                                <input type="file" name="image" id="editImageInput" accept="image/*">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <p>Cliquez ou glissez une image</p>
                                <p style="font-size:.75rem;color:var(--text-muted);">JPG, PNG, GIF, WebP • Max 5 Mo</p>
                            </div>
                            <div id="editImagePreview"></div>
                        </div>

                        <div style="display:flex;gap:14px;justify-content:flex-end;padding-top:20px;border-top:1px solid var(--border-color);">
                            <a href="<?= APP_URL ?>/signalement/<?= $signalement['id'] ?>" class="btn btn-outline"><i class="fas fa-times"></i> Annuler</a>
                            <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-save"></i> Enregistrer</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Danger Zone -->
            <div class="card" style="margin-top:20px;border:2px solid rgba(231,76,60,.2);border-radius:var(--radius-xl);">
                <div class="card-body" style="padding:24px;">
                    <div style="display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;">
                        <div>
                            <h4 style="color:var(--danger);font-size:.9rem;margin-bottom:4px;"><i class="fas fa-exclamation-triangle"></i> Zone dangereuse</h4>
                            <p style="font-size:.8rem;color:var(--text-muted);margin:0;">La suppression est irréversible.</p>
                        </div>
                        <form action="<?= APP_URL ?>/signalement/<?= $signalement['id'] ?>/supprimer" method="POST"
                            onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce signalement ? Cette action est irréversible.')">
                            <button type="submit" class="btn btn-danger btn-sm">
                                <i class="fas fa-trash"></i> Supprimer ce signalement
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<script>
document.getElementById('editFileZone').addEventListener('click',()=>document.getElementById('editImageInput').click());
document.getElementById('editImageInput').addEventListener('change',function(){
    const f=this.files[0];if(!f)return;
    const r=new FileReader();
    r.onload=e=>{document.getElementById('editImagePreview').innerHTML=`<img src="${e.target.result}" style="max-height:180px;border-radius:10px;margin-top:12px;">`;}
    r.readAsDataURL(f);
});
</script>
