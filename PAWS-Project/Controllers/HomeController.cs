using PAWSProject.Models;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using System.Web.Mvc;

namespace PAWS_Project.Controllers
{
    public class HomeController : Controller
    {
        private PawsContext db = new PawsContext();

        public ActionResult Index()
        {
            var pets = db.tblpet.ToList();
            return View(pets);
        }

        public ActionResult About()
        {
            ViewBag.Message = "Your application description page.";

            return View();
        }

        public ActionResult Contact()
        {
            ViewBag.Message = "Your contact page.";

            return View();
        }

        public ActionResult Donate()
        {
            ViewBag.Message = "Donate Page.";

            return View();
        }

        public ActionResult DonateBilling()
        {
            ViewBag.Message = "Donate Page.";

            return View();
        }

        public ActionResult Advocate()
        {
            ViewBag.Message = "Advocate Page.";

            return View();
        }

        public ActionResult AdvocacyNews1()
        {
            ViewBag.Message = "Advocacy Page.";

            return View();
        }

        public ActionResult AdvocacyNews2()
        {
            ViewBag.Message = "Advocacy Page.";

            return View();
        }

        public ActionResult LocalServices()
        {
            ViewBag.Message = "Local Services Page.";

            return View();
        }

        public ActionResult PrivacyPolicy()
        {
            ViewBag.Message = "Privacy Policy Page.";

            return View();
        }

        public ActionResult LegalInfo()
        {
            ViewBag.Message = "Legal Info Page.";

            return View();
        }

        public ActionResult Adopt()
        {
            var pets = db.tblpet.ToList();
            return View(pets);
        }

        public ActionResult Pet(int? id)
        {
            if (id == null)
            {
                return RedirectToAction("PageNotFound", "Home");
            }

            var pet = db.tblpet.Find(id);
            if (pet == null)
            {
                return RedirectToAction("PageNotFound", "Home");
            }

            return View(pet);
        }

        public ActionResult PageNotFound()
        {
            Response.StatusCode = 404;
            return View();
        }

        [HttpPost]
        public JsonResult SubmitAdoptionForm(tbladoptformModel adoptionForm)
        {
            if (Session["FormSubmitted"] != null)
            {
                return Json(new { success = false, message = "You can only submit an adoption form once." });
            }

            if (ModelState.IsValid)
            {
                adoptionForm.submitAt = DateTime.Now;
                db.tbladoptform.Add(adoptionForm);
                db.SaveChanges();

                // Set session variable to indicate form submission
                Session["FormSubmitted"] = true;

                return Json(new { success = true });
            }
            return Json(new { success = false, message = "Invalid form data" });
        }
    }
}
