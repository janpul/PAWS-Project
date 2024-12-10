using PAWSProject.Models;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using System.Web.Mvc;

namespace PAWSProject.Controllers
{
    public class AdminController : Controller
    {
        public ActionResult Index()
        {
            return View();
        }

        public ActionResult Error404()
        {
            return View();
        }

        public ActionResult Manage()
        {
            return View();
        }

        public ActionResult User()
        {
            return View();
        }

        [HttpPost]
        public ActionResult AddPet(tblpetModel pet, HttpPostedFileBase image)
        {
            if (ModelState.IsValid)
            {
                AddPetToDatabase(pet, image);
                return RedirectToAction("Index");
            }

            return View("Index");
        }

        private void AddPetToDatabase(tblpetModel pet, HttpPostedFileBase image)
        {
            var dateNow = DateTime.Now;

            if (image != null && image.ContentLength > 0)
            {
                var fileName = System.IO.Path.GetFileName(image.FileName);
                var path = System.IO.Path.Combine(Server.MapPath("~/Content/img/adopt/"), fileName);
                image.SaveAs(path);
                pet.imagePath = "/Content/img/adopt/" + fileName;
            }

            pet.createdAt = dateNow;
            pet.updateAt = dateNow;

            using (var connect = new PawsContext())
            {
                connect.tblpet.Add(pet);
                connect.SaveChanges();
            }
        }
    }
}