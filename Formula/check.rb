class Check < Formula
  desc "Allows retrieving transaction information from the database."
  homepage "Ajouter le lien du dossier github"
  url "https://github.com/tonpseudo/homebrew-check/archive/v1.0.0.tar.gz"
  sha256 "le_checksum_du_tar_gz"
  version "1.0.0"

  depends_on "php"

  def install
    bin.install "check.php" => "check"
  end
end
