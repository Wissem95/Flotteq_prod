// server/controllers/photoController.js

const Photo = require('../models/Photo');
const path = require('path');
const fs = require('fs');

exports.uploadPhoto = async (req, res) => {
  try {
    const photo = await Photo.create({
      vehicleId: req.body.vehicleId,
      filename: req.file.filename
    });
    res.status(201).json(photo);
  } catch (error) {
    console.error('Erreur upload photo:', error);
    res.status(500).json({ error: 'Erreur serveur' });
  }
};

exports.getPhotosByVehicle = async (req, res) => {
  try {
    const photos = await Photo.findAll({ where: { vehicleId: req.params.id } });
    res.json(photos);
  } catch (error) {
    console.error('Erreur récupération photos:', error);
    res.status(500).json({ error: 'Erreur serveur' });
  }
};

exports.deletePhoto = async (req, res) => {
  try {
    const photo = await Photo.findByPk(req.params.id);
    if (!photo) return res.status(404).json({ error: 'Photo non trouvée' });

    const filePath = path.join(__dirname, '..', 'uploads', 'vehicules', photo.filename);
    if (fs.existsSync(filePath)) fs.unlinkSync(filePath);

    await photo.destroy();
    res.json({ message: 'Photo supprimée' });
  } catch (error) {
    console.error('Erreur suppression photo:', error);
    res.status(500).json({ error: 'Erreur serveur' });
  }
};

